<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Analytics;
use App\Services\ImageService;
use App\Services\CacheService;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    private ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(Request $request): View
    {
        // 관리자인 경우 관리자 뷰로, 아닌 경우 공개 뷰로
        if (auth()->check() && $request->routeIs('admin.*')) {
            return $this->adminIndex($request);
        }
        
        // 캐시 키 생성
        $cacheParams = [
            'category' => $request->get('category'),
            'page' => $request->get('page', 1)
        ];
        $cacheKey = CacheService::getPostListCacheKey($cacheParams);
        
        // 캐시된 데이터 확인
        $posts = cache()->remember($cacheKey, config('optimize.post_list_cache_ttl', 1800), function () use ($request) {
            $query = Post::published()
                ->with(['category', 'tags', 'user'])
                ->latest('published_at');

            // 카테고리 필터링
            if ($request->filled('category')) {
                $category = Category::where('slug', $request->category)->first();
                if ($category) {
                    $query->where('category_id', $category->id);
                }
            }

            return $query->paginate(12);
        });

        return view('themes.default.posts.index', compact('posts'));
    }

    public function adminIndex(Request $request): View
    {
        $query = Post::with(['category', 'tags', 'user'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(20);
        $categories = Category::forPosts()->active()->get();

        return view('themes.default.admin.posts.index', compact('posts', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::forPosts()->active()->ordered()->get();
        $tags = Tag::withPosts()->get();

        return view('themes.default.admin.posts.create', compact('categories', 'tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'main_image' => 'nullable|image|mimes:jpeg,png,gif,webp|max:10240',
            'og_image' => 'nullable|image|mimes:jpeg,png,gif,webp|max:10240',
            
            // SEO 메타 데이터
            'seo.og_title' => 'nullable|string|max:255',
            'seo.og_description' => 'nullable|string|max:500',
            'seo.meta_keywords' => 'nullable|string',
            'seo.robots' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            $postData = [
                'title' => $validated['title'],
                'content' => $validated['content'],
                'summary' => $validated['summary'],
                'category_id' => $validated['category_id'],
                'status' => $validated['status'],
                'user_id' => Auth::id(),
            ];

            // 발행 시간 설정
            if ($validated['status'] === 'published') {
                $postData['published_at'] = $validated['published_at'] ?? now();
            }

            // 대표 이미지 업로드
            if ($request->hasFile('main_image')) {
                $imageResult = $this->imageService->uploadMainImage($request->file('main_image'));
                $postData['main_image'] = $imageResult['path'];
            }

            // OG 이미지 업로드
            if ($request->hasFile('og_image')) {
                $ogImageResult = $this->imageService->uploadOgImage($request->file('og_image'));
                $postData['og_image'] = $ogImageResult['path'];
            }

            $post = Post::create($postData);

            // 태그 연결
            if (!empty($validated['tags'])) {
                $post->tags()->attach($validated['tags']);
                
                // 태그 카운트 업데이트
                foreach ($validated['tags'] as $tagId) {
                    $tag = Tag::find($tagId);
                    $tag?->updatePostCount();
                }
            }

            // SEO 메타 데이터 저장
            if (!empty($validated['seo'])) {
                $seoData = array_merge($validated['seo'], ['post_id' => $post->id]);
                $post->seoMeta()->create($seoData);
            }

            DB::commit();
            
            // 캐시 무효화
            CacheService::invalidatePostCache($post->id);

            return redirect()->route('admin.posts.show', $post)
                ->with('success', '포스트가 성공적으로 생성되었습니다.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors(['error' => '포스트 생성 중 오류가 발생했습니다: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Request $request, Post $post): View
    {
        // 관리자인 경우 관리자 뷰로
        if (auth()->check() && $request->routeIs('admin.*')) {
            return $this->adminShow($post);
        }

        // 공개되지 않은 포스트는 관리자만 볼 수 있음
        if (!$post->is_published && (!auth()->check() || !auth()->user()->hasRole('admin'))) {
            abort(404);
        }

        $post->load(['category', 'tags', 'user', 'seoMeta', 'attachments']);
        
        // 조회수 증가 및 접속 통계 기록
        $post->incrementViews();
        Analytics::recordPageView($post, $request, auth()->user());
        
        return view('themes.default.posts.show', compact('post'));
    }

    public function adminShow(Post $post): View
    {
        $post->load(['category', 'tags', 'user', 'seoMeta', 'attachments']);
        
        return view('themes.default.admin.posts.show', compact('post'));
    }

    public function edit(Post $post): View
    {
        $post->load(['category', 'tags', 'seoMeta']);
        $categories = Category::forPosts()->active()->ordered()->get();
        $tags = Tag::withPosts()->get();

        return view('themes.default.admin.posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'main_image' => 'nullable|image|mimes:jpeg,png,gif,webp|max:10240',
            'og_image' => 'nullable|image|mimes:jpeg,png,gif,webp|max:10240',
            'remove_main_image' => 'nullable|boolean',
            'remove_og_image' => 'nullable|boolean',
            
            // SEO 메타 데이터
            'seo.og_title' => 'nullable|string|max:255',
            'seo.og_description' => 'nullable|string|max:500',
            'seo.meta_keywords' => 'nullable|string',
            'seo.robots' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            $postData = [
                'title' => $validated['title'],
                'content' => $validated['content'],
                'summary' => $validated['summary'],
                'category_id' => $validated['category_id'],
                'status' => $validated['status'],
            ];

            // 발행 상태 변경 시 발행 시간 설정
            if ($validated['status'] === 'published' && $post->status !== 'published') {
                $postData['published_at'] = $validated['published_at'] ?? now();
            }

            // 기존 대표 이미지 삭제
            if ($request->boolean('remove_main_image') && $post->main_image) {
                $this->imageService->deleteImageWithThumbnail($post->main_image);
                $postData['main_image'] = null;
            }

            // 새 대표 이미지 업로드
            if ($request->hasFile('main_image')) {
                if ($post->main_image) {
                    $this->imageService->deleteImageWithThumbnail($post->main_image);
                }
                $imageResult = $this->imageService->uploadMainImage($request->file('main_image'));
                $postData['main_image'] = $imageResult['path'];
            }

            // 기존 OG 이미지 삭제
            if ($request->boolean('remove_og_image') && $post->og_image) {
                $this->imageService->deleteImage($post->og_image);
                $postData['og_image'] = null;
            }

            // 새 OG 이미지 업로드
            if ($request->hasFile('og_image')) {
                if ($post->og_image) {
                    $this->imageService->deleteImage($post->og_image);
                }
                $ogImageResult = $this->imageService->uploadOgImage($request->file('og_image'));
                $postData['og_image'] = $ogImageResult['path'];
            }

            $post->update($postData);

            // 기존 태그 관계 업데이트
            $oldTagIds = $post->tags->pluck('id')->toArray();
            $newTagIds = $validated['tags'] ?? [];

            $post->tags()->sync($newTagIds);

            // 태그 카운트 업데이트 (변경된 태그들만)
            $changedTagIds = array_unique(array_merge($oldTagIds, $newTagIds));
            foreach ($changedTagIds as $tagId) {
                $tag = Tag::find($tagId);
                $tag?->updatePostCount();
            }

            // SEO 메타 데이터 업데이트
            if (!empty($validated['seo'])) {
                $post->seoMeta()->updateOrCreate(
                    ['post_id' => $post->id],
                    $validated['seo']
                );
            }

            DB::commit();
            
            // 캐시 무효화
            CacheService::invalidatePostCache($post->id);

            return redirect()->route('admin.posts.show', $post)
                ->with('success', '포스트가 성공적으로 수정되었습니다.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors(['error' => '포스트 수정 중 오류가 발생했습니다: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Post $post): RedirectResponse
    {
        DB::beginTransaction();

        try {
            // 연결된 태그들의 카운트 업데이트
            $tagIds = $post->tags->pluck('id')->toArray();

            // 이미지 파일 삭제
            if ($post->main_image) {
                $this->imageService->deleteImageWithThumbnail($post->main_image);
            }
            
            if ($post->og_image) {
                $this->imageService->deleteImage($post->og_image);
            }

            // 포스트 삭제 (연관 데이터는 외래키 제약조건으로 자동 삭제)
            $post->delete();

            // 태그 카운트 업데이트
            foreach ($tagIds as $tagId) {
                $tag = Tag::find($tagId);
                $tag?->updatePostCount();
            }

            DB::commit();
            
            // 캐시 무효화
            CacheService::invalidatePostCache($post->id);

            return redirect()->route('admin.posts.index')
                ->with('success', '포스트가 성공적으로 삭제되었습니다.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors(['error' => '포스트 삭제 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }
    }

    public function uploadContentImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,gif,webp|max:5120'
        ]);

        try {
            $result = $this->imageService->uploadContentImage($request->file('image'));
            
            return response()->json([
                'success' => true,
                'url' => $result['url'],
                'path' => $result['path'],
                'size' => $result['size'],
                'dimensions' => [
                    'width' => $result['width'],
                    'height' => $result['height']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API: 포스트 목록 조회
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $query = Post::published()
            ->with(['category', 'tags', 'user'])
            ->latest('published_at');

        // 카테고리 필터링
        if ($request->filled('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // 태그 필터링
        if ($request->filled('tag')) {
            $tag = Tag::where('slug', $request->tag)->first();
            if ($tag) {
                $query->whereHas('tags', function ($q) use ($tag) {
                    $q->where('tags.id', $tag->id);
                });
            }
        }

        // 검색
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => PostResource::collection($posts),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ]);
    }

    /**
     * API: 포스트 상세 조회
     */
    public function apiShow(Post $post): JsonResponse
    {
        $post->load(['category', 'tags', 'user', 'seoMeta']);
        
        // 조회수 증가
        $post->increment('views_count');

        return response()->json([
            'success' => true,
            'data' => new PostResource($post)
        ]);
    }

    /**
     * API: 관련 포스트 조회
     */
    public function related(Post $post): JsonResponse
    {
        $relatedPosts = Post::published()
            ->where('id', '!=', $post->id)
            ->where(function ($query) use ($post) {
                // 같은 카테고리
                $query->where('category_id', $post->category_id);
                
                // 또는 공통 태그가 있는 포스트
                if ($post->tags->count() > 0) {
                    $query->orWhereHas('tags', function ($q) use ($post) {
                        $q->whereIn('tags.id', $post->tags->pluck('id'));
                    });
                }
            })
            ->with(['category', 'tags', 'user'])
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => PostResource::collection($relatedPosts)
        ]);
    }
}