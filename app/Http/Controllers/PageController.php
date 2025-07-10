<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Http\Resources\PageResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * 특정 페이지 표시
     * 
     * @param Page $page
     * @return View
     */
    public function show(Page $page): View
    {
        // 비공개 페이지는 접근 불가
        if (!$page->is_published) {
            abort(404);
        }

        // 페이지 조회수 증가 (캐시된 값 사용)
        $this->incrementViews($page);

        // 동일 카테고리의 다른 페이지들 (사이드바용)
        $relatedPages = null;
        if ($page->category) {
            $relatedPages = Page::where('category_id', $page->category_id)
                ->where('id', '!=', $page->id)
                ->where('is_published', true)
                ->orderBy('order')
                ->limit(5)
                ->get();
        }

        return view('themes.default.pages.show', compact('page', 'relatedPages'));
    }

    /**
     * 페이지 목록 표시
     */
    public function index(Request $request): View
    {
        $pages = Page::where('is_published', true)
            ->with('category')
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view(themed('pages.index'), compact('pages'));
    }

    /**
     * API: 페이지 목록 조회
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $query = Page::where('is_published', true)
            ->with('category');

        // 카테고리 필터링
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // 정렬
        $sortBy = $request->get('sort', 'order');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        if ($sortBy !== 'created_at') {
            $query->orderBy('created_at', 'desc');
        }

        $pages = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => PageResource::collection($pages),
            'meta' => [
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
                'per_page' => $pages->perPage(),
                'total' => $pages->total(),
            ]
        ]);
    }

    /**
     * API: 페이지 상세 조회
     */
    public function apiShow(Page $page): JsonResponse
    {
        if (!$page->is_published) {
            return response()->json([
                'success' => false,
                'message' => '페이지를 찾을 수 없습니다.'
            ], 404);
        }

        $page->load('category');
        
        // 조회수 증가
        $this->incrementViews($page);

        return response()->json([
            'success' => true,
            'data' => new PageResource($page)
        ]);
    }

    /**
     * API: 형제 페이지 조회
     */
    public function siblings(Page $page): JsonResponse
    {
        $siblings = Page::where('is_published', true)
            ->where('id', '!=', $page->id)
            ->where('category_id', $page->category_id)
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => PageResource::collection($siblings)
        ]);
    }

    /**
     * API: 하위 페이지 조회
     */
    public function children(Page $page): JsonResponse
    {
        $children = Page::where('is_published', true)
            ->where('parent_id', $page->id)
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => PageResource::collection($children)
        ]);
    }

    /**
     * API: 관련 페이지 조회
     */
    public function related(Page $page): JsonResponse
    {
        $related = Page::where('is_published', true)
            ->where('id', '!=', $page->id)
            ->where(function ($query) use ($page) {
                // 같은 카테고리
                $query->where('category_id', $page->category_id);
                
                // 또는 제목에 유사한 키워드가 있는 페이지
                $keywords = explode(' ', $page->title);
                foreach ($keywords as $keyword) {
                    if (strlen($keyword) > 2) {
                        $query->orWhere('title', 'like', "%{$keyword}%")
                              ->orWhere('content', 'like', "%{$keyword}%");
                    }
                }
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => PageResource::collection($related)
        ]);
    }

    /**
     * 페이지 조회수 증가 (중복 방지)
     * 
     * @param Page $page
     * @return void
     */
    private function incrementViews(Page $page): void
    {
        $sessionKey = 'page_viewed_' . $page->id;
        
        // 세션에 이미 조회 기록이 있으면 증가하지 않음
        if (!session()->has($sessionKey)) {
            // 조회수 증가는 큐로 처리하거나 캐시를 통해 배치 처리
            $page->increment('views_count');
            session()->put($sessionKey, true);
        }
    }
}