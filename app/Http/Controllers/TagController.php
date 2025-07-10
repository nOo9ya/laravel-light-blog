<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Post;
use App\Http\Resources\TagResource;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TagController extends Controller
{
    /**
     * 태그별 포스트 목록 표시
     * 
     * @param Tag $tag
     * @param Request $request
     * @return View
     */
    public function show(Tag $tag, Request $request): View
    {
        // 태그와 관련된 포스트 조회
        $posts = Post::published()
            ->whereHas('tags', function ($query) use ($tag) {
                $query->where('tags.id', $tag->id);
            })
            ->with(['category', 'tags', 'user'])
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        // 관련 태그들 (동일한 포스트에 사용된 다른 태그들)
        $relatedTags = Tag::whereHas('posts', function ($query) use ($tag) {
                $query->whereHas('tags', function ($subQuery) use ($tag) {
                    $subQuery->where('tags.id', $tag->id);
                });
            })
            ->where('id', '!=', $tag->id)
            ->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(10)
            ->get();

        // 인기 태그들 (사이드바용)
        $popularTags = Tag::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(15)
            ->get();

        return view('themes.default.tags.show', compact(
            'tag',
            'posts',
            'relatedTags',
            'popularTags'
        ));
    }

    /**
     * 모든 태그 목록 표시 (태그 클라우드)
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // 태그를 포스트 수별로 정렬
        $tags = Tag::withCount('posts')
            ->having('posts_count', '>', 0)
            ->orderBy('posts_count', 'desc')
            ->get();

        // 태그 클라우드용 가중치 계산
        $maxCount = $tags->max('posts_count') ?: 1;
        $minCount = $tags->min('posts_count') ?: 1;

        $tags = $tags->map(function ($tag) use ($maxCount, $minCount) {
            // 1부터 5까지의 크기 레벨 계산
            if ($maxCount === $minCount) {
                $tag->size_level = 3;
            } else {
                $tag->size_level = ceil(5 * (($tag->posts_count - $minCount) / ($maxCount - $minCount))) ?: 1;
            }
            return $tag;
        });

        return view(themed('tags.index'), compact('tags'));
    }

    /**
     * 태그별 포스트 목록
     */
    public function posts(Tag $tag, Request $request): View
    {
        $posts = Post::published()
            ->whereHas('tags', function ($query) use ($tag) {
                $query->where('tags.id', $tag->id);
            })
            ->with(['category', 'tags', 'user'])
            ->latest('published_at')
            ->paginate(12);

        $relatedTags = Tag::whereHas('posts', function ($query) use ($tag) {
                $query->whereHas('tags', function ($subQuery) use ($tag) {
                    $subQuery->where('tags.id', $tag->id);
                });
            })
            ->where('id', '!=', $tag->id)
            ->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(8)
            ->get();

        return view(themed('tags.posts'), compact('tag', 'posts', 'relatedTags'));
    }

    /**
     * API: 태그 목록 조회
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $query = Tag::query();

        // 포스트가 있는 태그만 조회
        if ($request->boolean('has_posts', true)) {
            $query->withCount('posts')->having('posts_count', '>', 0);
        }

        // 정렬
        $sortBy = $request->get('sort', 'posts_count');
        $sortDirection = $request->get('direction', 'desc');
        
        if ($sortBy === 'posts_count') {
            $query->withCount('posts')->orderBy('posts_count', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $tags = $query->limit($request->get('limit', 50))->get();

        return response()->json([
            'success' => true,
            'data' => TagResource::collection($tags)
        ]);
    }

    /**
     * API: 태그 상세 조회
     */
    public function apiShow(Tag $tag): JsonResponse
    {
        $tag->loadCount('posts');

        return response()->json([
            'success' => true,
            'data' => new TagResource($tag)
        ]);
    }

    /**
     * API: 유사한 태그 조회
     */
    public function similar(Tag $tag): JsonResponse
    {
        $similarTags = Tag::whereHas('posts', function ($query) use ($tag) {
                $query->whereHas('tags', function ($subQuery) use ($tag) {
                    $subQuery->where('tags.id', $tag->id);
                });
            })
            ->where('id', '!=', $tag->id)
            ->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => TagResource::collection($similarTags)
        ]);
    }

    /**
     * API: 태그의 인기 포스트 조회
     */
    public function trendingPosts(Tag $tag): JsonResponse
    {
        $posts = Post::published()
            ->whereHas('tags', function ($query) use ($tag) {
                $query->where('tags.id', $tag->id);
            })
            ->with(['category', 'tags', 'user'])
            ->orderBy('views_count', 'desc')
            ->orderBy('published_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => PostResource::collection($posts)
        ]);
    }
}