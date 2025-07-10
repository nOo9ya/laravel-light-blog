<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Services\CacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * 카테고리 목록 표시
     */
    public function index(): View
    {
        $categories = Category::with('parent')
            ->withCount('posts')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        // 계층형 구조로 정렬
        $categoriesTree = $this->buildCategoryTree($categories);

        return view('admin.categories.index', compact('categoriesTree', 'categories'));
    }

    /**
     * 카테고리 생성 폼
     */
    public function create(): View
    {
        $parentCategories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * 카테고리 저장
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        // 캐시 무효화
        CacheService::invalidateCategoryCache();

        return redirect()->route('admin.categories.index')
            ->with('success', '카테고리가 성공적으로 생성되었습니다.');
    }

    /**
     * 카테고리 상세 보기
     */
    public function show(Category $category): View
    {
        $category->load(['parent', 'children', 'posts']);
        
        // 카테고리 통계
        $stats = [
            'total_posts' => $category->posts()->count(),
            'published_posts' => $category->posts()->where('status', 'published')->count(),
            'draft_posts' => $category->posts()->where('status', 'draft')->count(),
            'child_categories' => $category->children()->count(),
        ];

        return view('admin.categories.show', compact('category', 'stats'));
    }

    /**
     * 카테고리 수정 폼
     */
    public function edit(Category $category): View
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        // 현재 카테고리의 하위 카테고리들을 부모 후보에서 제외
        $childIds = $this->getAllChildIds($category);
        $parentCategories = $parentCategories->whereNotIn('id', $childIds);

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * 카테고리 업데이트
     */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        // 캐시 무효화
        CacheService::invalidateCategoryCache();

        return redirect()->route('admin.categories.index')
            ->with('success', '카테고리가 성공적으로 수정되었습니다.');
    }

    /**
     * 카테고리 삭제
     */
    public function destroy(Category $category): RedirectResponse
    {
        // 하위 카테고리가 있는지 확인
        if ($category->children()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', '하위 카테고리가 있는 카테고리는 삭제할 수 없습니다.');
        }

        // 포스트가 있는지 확인
        if ($category->posts()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', '포스트가 있는 카테고리는 삭제할 수 없습니다.');
        }

        $category->delete();

        // 캐시 무효화
        CacheService::invalidateCategoryCache();

        return redirect()->route('admin.categories.index')
            ->with('success', '카테고리가 성공적으로 삭제되었습니다.');
    }

    /**
     * 계층형 트리 구조 생성
     */
    private function buildCategoryTree($categories, $parentId = null): array
    {
        $tree = [];

        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $category->children_tree = $this->buildCategoryTree($categories, $category->id);
                $tree[] = $category;
            }
        }

        return $tree;
    }

    /**
     * 카테고리의 모든 하위 카테고리 ID 조회
     */
    private function getAllChildIds(Category $category): array
    {
        $childIds = [];
        $directChildren = $category->children;

        foreach ($directChildren as $child) {
            $childIds[] = $child->id;
            $childIds = array_merge($childIds, $this->getAllChildIds($child));
        }

        return $childIds;
    }
}