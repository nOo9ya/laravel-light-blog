<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Page;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * 카테고리별 콘텐츠 목록 표시
     * 
     * @param Category $category
     * @param Request $request
     * @return View
     */
    public function show(Category $category, Request $request): View
    {
        // 비활성 카테고리는 접근 불가
        if (!$category->is_active) {
            abort(404);
        }

        // 카테고리 타입에 따라 콘텐츠 조회
        $posts = collect();
        $pages = collect();

        if ($category->type === 'post' || $category->type === 'both') {
            $posts = $this->getCategoryPosts($category, $request);
        }

        if ($category->type === 'page' || $category->type === 'both') {
            $pages = $this->getCategoryPages($category, $request);
        }

        // 하위 카테고리 목록
        $childCategories = Category::where('parent_id', $category->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // 부모 카테고리 경로 (브레드크럼용)
        $breadcrumbs = $this->getBreadcrumbs($category);

        return view('themes.default.categories.show', compact(
            'category',
            'posts',
            'pages',
            'childCategories',
            'breadcrumbs'
        ));
    }

    /**
     * 카테고리의 포스트 목록 조회
     * 
     * @param Category $category
     * @param Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    private function getCategoryPosts(Category $category, Request $request)
    {
        // 현재 카테고리와 모든 하위 카테고리 ID 수집
        $categoryIds = $this->getAllChildCategoryIds($category);

        return Post::published()
            ->whereIn('category_id', $categoryIds)
            ->with(['category', 'tags', 'user'])
            ->orderBy('published_at', 'desc')
            ->paginate(12, ['*'], 'posts_page');
    }

    /**
     * 카테고리의 페이지 목록 조회
     * 
     * @param Category $category
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getCategoryPages(Category $category, Request $request)
    {
        // 현재 카테고리와 모든 하위 카테고리 ID 수집
        $categoryIds = $this->getAllChildCategoryIds($category);

        return Page::where('is_published', true)
            ->whereIn('category_id', $categoryIds)
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * 카테고리와 모든 하위 카테고리 ID 조회
     * 
     * @param Category $category
     * @return array
     */
    private function getAllChildCategoryIds(Category $category): array
    {
        $categoryIds = [$category->id];

        // 재귀적으로 모든 하위 카테고리 ID 수집
        $childIds = Category::where('parent_id', $category->id)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        foreach ($childIds as $childId) {
            $childCategory = Category::find($childId);
            if ($childCategory) {
                $categoryIds = array_merge($categoryIds, $this->getAllChildCategoryIds($childCategory));
            }
        }

        return array_unique($categoryIds);
    }

    /**
     * 카테고리 브레드크럼 경로 생성
     * 
     * @param Category $category
     * @return array
     */
    private function getBreadcrumbs(Category $category): array
    {
        $breadcrumbs = [];
        $current = $category;

        // 부모를 따라 올라가면서 경로 구성
        while ($current) {
            array_unshift($breadcrumbs, [
                'name' => $current->name,
                'url' => route('categories.show', $current->slug),
                'slug' => $current->slug
            ]);

            $current = $current->parent;
        }

        return $breadcrumbs;
    }

    /**
     * 카테고리 목록 표시
     */
    public function index(Request $request): View
    {
        $categories = Category::where('is_active', true)
            ->where('type', 'post')
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            }])
            ->withCount(['posts' => function ($query) {
                $query->where('status', 'published');
            }])
            ->orderBy('sort_order')
            ->get();

        return view(themed('categories.index'), compact('categories'));
    }

    /**
     * 카테고리별 포스트 목록
     */
    public function posts(Category $category, Request $request): View
    {
        // 하위 카테고리 포함 여부
        $includeChildren = $request->boolean('include_children', true);
        $categoryIds = $includeChildren ? $this->getAllChildCategoryIds($category) : [$category->id];

        $posts = Post::published()
            ->whereIn('category_id', $categoryIds)
            ->with(['category', 'tags', 'user'])
            ->latest('published_at')
            ->paginate(12);

        $breadcrumbs = $this->getBreadcrumbs($category);

        return view(themed('categories.posts'), compact('category', 'posts', 'breadcrumbs'));
    }

    /**
     * API: 카테고리 목록 조회
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $query = Category::where('is_active', true);

        // 타입 필터링
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // 계층 구조 여부
        if ($request->boolean('hierarchical', false)) {
            $categories = $query->whereNull('parent_id')
                ->with(['children' => function ($q) {
                    $q->where('is_active', true)->orderBy('sort_order');
                }])
                ->orderBy('sort_order')
                ->get();
        } else {
            $categories = $query->orderBy('sort_order')->get();
        }

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories)
        ]);
    }

    /**
     * API: 카테고리 상세 조회
     */
    public function apiShow(Category $category): JsonResponse
    {
        $category->load(['parent', 'children', 'posts' => function ($query) {
            $query->published()->latest('published_at')->limit(10);
        }]);

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category)
        ]);
    }

    /**
     * API: 하위 카테고리 조회
     */
    public function children(Category $category): JsonResponse
    {
        $children = $category->children()
            ->where('is_active', true)
            ->withCount(['posts' => function ($query) {
                $query->where('status', 'published');
            }])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($children)
        ]);
    }

    /**
     * API: 카테고리 최근 포스트 조회
     */
    public function recentPosts(Category $category): JsonResponse
    {
        $categoryIds = $this->getAllChildCategoryIds($category);

        $posts = Post::published()
            ->whereIn('category_id', $categoryIds)
            ->with(['category', 'tags', 'user'])
            ->latest('published_at')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => PostResource::collection($posts)
        ]);
    }
}