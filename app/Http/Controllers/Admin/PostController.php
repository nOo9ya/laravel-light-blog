<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Services\CacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * 포스트 목록 표시
     */
    public function index(Request $request): View
    {
        $query = Post::with(['category', 'tags', 'user']);

        // 검색 필터
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // 상태 필터
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // 카테고리 필터
        if ($request->filled('category')) {
            $query->where('category_id', $request->get('category'));
        }

        $posts = $query->latest()->paginate(20);
        $categories = Category::orderBy('name')->get();

        return view('admin.posts.index', compact('posts', 'categories'));
    }

    /**
     * 포스트 생성 폼
     */
    public function create(): View
    {
        $categories = Category::where('type', 'post')
            ->orWhere('type', 'both')
            ->orderBy('name')
            ->get();
        
        $tags = Tag::orderBy('name')->get();

        return view('admin.posts.create', compact('categories', 'tags'));
    }

    /**
     * 포스트 저장
     */
    public function store(PostRequest $request): RedirectResponse
    {
        $data = $request->validated();
        
        // 이미지 업로드 처리
        if ($request->hasFile('main_image')) {
            $data['main_image'] = $this->uploadImage($request->file('main_image'), 'main');
        }
        
        if ($request->hasFile('og_image')) {
            $data['og_image'] = $this->uploadImage($request->file('og_image'), 'og');
        }

        // 포스트 생성
        $post = auth()->user()->posts()->create($data);

        // 태그 동기화
        if (!empty($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }

        // SEO 메타 데이터 저장
        if (!empty($data['seo'])) {
            $post->seoMeta()->create($data['seo']);
        }

        // 캐시 무효화
        CacheService::invalidatePostCache();

        return redirect()->route('admin.posts.index')
            ->with('success', '포스트가 성공적으로 생성되었습니다.');
    }

    /**
     * 포스트 상세 보기
     */
    public function show(Post $post): View
    {
        $post->load(['category', 'tags', 'user', 'seoMeta']);
        return view('admin.posts.show', compact('post'));
    }

    /**
     * 포스트 수정 폼
     */
    public function edit(Post $post): View
    {
        $post->load(['category', 'tags', 'seoMeta']);
        
        $categories = Category::where('type', 'post')
            ->orWhere('type', 'both')
            ->orderBy('name')
            ->get();
        
        $tags = Tag::orderBy('name')->get();

        return view('admin.posts.edit', compact('post', 'categories', 'tags'));
    }

    /**
     * 포스트 업데이트
     */
    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $data = $request->validated();
        
        // 이미지 업로드 처리
        if ($request->hasFile('main_image')) {
            $data['main_image'] = $this->uploadImage($request->file('main_image'), 'main');
        }
        
        if ($request->hasFile('og_image')) {
            $data['og_image'] = $this->uploadImage($request->file('og_image'), 'og');
        }

        // 포스트 업데이트
        $post->update($data);

        // 태그 동기화
        if (isset($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }

        // SEO 메타 데이터 업데이트
        if (!empty($data['seo'])) {
            $post->seoMeta()->updateOrCreate(
                ['post_id' => $post->id],
                $data['seo']
            );
        }

        // 캐시 무효화
        CacheService::invalidatePostCache();

        return redirect()->route('admin.posts.index')
            ->with('success', '포스트가 성공적으로 수정되었습니다.');
    }

    /**
     * 포스트 삭제
     */
    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        // 캐시 무효화
        CacheService::invalidatePostCache();

        return redirect()->route('admin.posts.index')
            ->with('success', '포스트가 성공적으로 삭제되었습니다.');
    }

    /**
     * 이미지 업로드 처리
     */
    private function uploadImage($file, string $type): string
    {
        $path = $file->store('posts/' . $type, 'public');
        
        // ImageService를 통한 이미지 최적화 처리
        if (class_exists(\App\Services\ImageService::class)) {
            $imageService = app(\App\Services\ImageService::class);
            return $imageService->processImage($path, $type);
        }
        
        return $path;
    }
}