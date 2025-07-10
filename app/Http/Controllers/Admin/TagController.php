<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TagRequest;
use App\Models\Tag;
use App\Services\CacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * 태그 목록 표시
     */
    public function index(Request $request): View
    {
        $query = Tag::withCount('posts');

        // 검색 필터
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tags = $query->orderBy('posts_count', 'desc')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.tags.index', compact('tags'));
    }

    /**
     * 태그 생성 폼
     */
    public function create(): View
    {
        return view('admin.tags.create');
    }

    /**
     * 태그 저장
     */
    public function store(TagRequest $request): RedirectResponse
    {
        Tag::create($request->validated());

        // 캐시 무효화
        CacheService::invalidateTagCache();

        return redirect()->route('admin.tags.index')
            ->with('success', '태그가 성공적으로 생성되었습니다.');
    }

    /**
     * 태그 상세 보기
     */
    public function show(Tag $tag): View
    {
        $tag->load('posts');
        
        // 태그 통계
        $stats = [
            'total_posts' => $tag->posts()->count(),
            'published_posts' => $tag->posts()->where('status', 'published')->count(),
            'recent_posts' => $tag->posts()
                ->where('status', 'published')
                ->orderBy('published_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return view('admin.tags.show', compact('tag', 'stats'));
    }

    /**
     * 태그 수정 폼
     */
    public function edit(Tag $tag): View
    {
        return view('admin.tags.edit', compact('tag'));
    }

    /**
     * 태그 업데이트
     */
    public function update(TagRequest $request, Tag $tag): RedirectResponse
    {
        $tag->update($request->validated());

        // 캐시 무효화
        CacheService::invalidateTagCache();

        return redirect()->route('admin.tags.index')
            ->with('success', '태그가 성공적으로 수정되었습니다.');
    }

    /**
     * 태그 삭제
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        // 포스트와의 관계 해제
        $tag->posts()->detach();

        $tag->delete();

        // 캐시 무효화
        CacheService::invalidateTagCache();

        return redirect()->route('admin.tags.index')
            ->with('success', '태그가 성공적으로 삭제되었습니다.');
    }

    /**
     * 태그 일괄 삭제
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'exists:tags,id'
        ]);

        $tagIds = $request->get('tag_ids');
        
        // 선택된 태그들과 포스트 관계 해제
        foreach ($tagIds as $tagId) {
            $tag = Tag::find($tagId);
            if ($tag) {
                $tag->posts()->detach();
            }
        }

        // 태그 삭제
        Tag::whereIn('id', $tagIds)->delete();

        // 캐시 무효화
        CacheService::invalidateTagCache();

        return redirect()->route('admin.tags.index')
            ->with('success', count($tagIds) . '개의 태그가 성공적으로 삭제되었습니다.');
    }

    /**
     * 사용되지 않는 태그 정리
     */
    public function cleanup(): RedirectResponse
    {
        $unusedTags = Tag::doesntHave('posts')->get();
        $count = $unusedTags->count();

        if ($count > 0) {
            Tag::doesntHave('posts')->delete();
            
            // 캐시 무효화
            CacheService::invalidateTagCache();

            return redirect()->route('admin.tags.index')
                ->with('success', "{$count}개의 사용되지 않는 태그가 정리되었습니다.");
        }

        return redirect()->route('admin.tags.index')
            ->with('info', '정리할 태그가 없습니다.');
    }
}