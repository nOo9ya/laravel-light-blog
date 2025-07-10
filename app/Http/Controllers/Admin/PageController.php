<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageRequest;
use App\Models\Page;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * 페이지 목록 표시
     */
    public function index(Request $request): View
    {
        $query = Page::with(['category', 'user', 'parent'])
            ->withCount('children');

        // 검색 필터
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        // 상태 필터
        if ($request->filled('status')) {
            $query->where('is_published', $request->get('status') === 'published');
        }

        // 카테고리 필터
        if ($request->filled('category')) {
            $query->where('category_id', $request->get('category'));
        }

        // 템플릿 필터
        if ($request->filled('template')) {
            $query->where('template', $request->get('template'));
        }

        // 정렬
        $sortBy = $request->get('sort', 'updated_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if ($sortBy === 'hierarchy') {
            $query->orderBy('sort_order')->orderBy('title');
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $pages = $query->paginate(20)->withQueryString();
        $categories = Category::where('is_active', true)->get();

        return view('admin.pages.index', compact('pages', 'categories'));
    }

    /**
     * 페이지 생성 폼 표시
     */
    public function create(): View
    {
        $categories = Category::where('is_active', true)->get();
        $parentPages = Page::where('is_published', true)->get();
        $templates = $this->getAvailableTemplates();

        return view('admin.pages.create', compact('categories', 'parentPages', 'templates'));
    }

    /**
     * 새 페이지 저장
     */
    public function store(PageRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        // 슬러그 생성
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }
        
        // 중복 슬러그 체크
        $validated['slug'] = $this->ensureUniqueSlug($validated['slug']);
        
        // 사용자 ID 설정
        $validated['user_id'] = auth()->id();
        
        // 읽기 시간 계산
        $validated['reading_time'] = $this->calculateReadingTime($validated['content']);

        $page = Page::create($validated);

        return redirect()->route('admin.pages.index')
            ->with('success', '페이지가 성공적으로 생성되었습니다.');
    }

    /**
     * 페이지 상세 보기
     */
    public function show(Page $page): View
    {
        $page->load(['category', 'user', 'parent', 'children']);
        
        return view('admin.pages.show', compact('page'));
    }

    /**
     * 페이지 수정 폼 표시
     */
    public function edit(Page $page): View
    {
        $categories = Category::where('is_active', true)->get();
        $parentPages = Page::where('is_published', true)
            ->where('id', '!=', $page->id)
            ->get();
        $templates = $this->getAvailableTemplates();

        return view('admin.pages.edit', compact('page', 'categories', 'parentPages', 'templates'));
    }

    /**
     * 페이지 업데이트
     */
    public function update(PageRequest $request, Page $page): RedirectResponse
    {
        $validated = $request->validated();
        
        // 슬러그가 변경된 경우 중복 체크
        if (!empty($validated['slug']) && $validated['slug'] !== $page->slug) {
            $validated['slug'] = $this->ensureUniqueSlug($validated['slug'], $page->id);
        }
        
        // 읽기 시간 재계산
        $validated['reading_time'] = $this->calculateReadingTime($validated['content']);

        $page->update($validated);

        return redirect()->route('admin.pages.index')
            ->with('success', '페이지가 성공적으로 수정되었습니다.');
    }

    /**
     * 페이지 삭제
     */
    public function destroy(Page $page): RedirectResponse
    {
        // 하위 페이지가 있는지 확인
        if ($page->children()->count() > 0) {
            return redirect()->route('admin.pages.index')
                ->with('error', '하위 페이지가 있는 페이지는 삭제할 수 없습니다.');
        }

        $title = $page->title;
        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', "'{$title}' 페이지가 삭제되었습니다.");
    }

    /**
     * 페이지 복제
     */
    public function duplicate(Page $page): RedirectResponse
    {
        $newPage = $page->replicate();
        $newPage->title = $page->title . ' (복사본)';
        $newPage->slug = $this->ensureUniqueSlug($page->slug . '-copy');
        $newPage->is_published = false;
        $newPage->user_id = auth()->id();
        $newPage->save();

        return redirect()->route('admin.pages.edit', $newPage)
            ->with('success', '페이지가 복제되었습니다.');
    }

    /**
     * 페이지 순서 변경
     */
    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'pages' => 'required|array',
            'pages.*.id' => 'required|exists:pages,id',
            'pages.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->get('pages') as $pageData) {
            Page::where('id', $pageData['id'])
                ->update(['sort_order' => $pageData['sort_order']]);
        }

        return redirect()->route('admin.pages.index')
            ->with('success', '페이지 순서가 변경되었습니다.');
    }

    /**
     * 일괄 작업
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:publish,unpublish,delete,change_template',
            'page_ids' => 'required|array',
            'page_ids.*' => 'exists:pages,id',
            'template' => 'required_if:action,change_template|string',
        ], [
            'action.required' => '작업을 선택해주세요.',
            'action.in' => '올바른 작업을 선택해주세요.',
            'page_ids.required' => '페이지를 선택해주세요.',
            'template.required_if' => '템플릿을 선택해주세요.',
        ]);

        $pageIds = $request->get('page_ids');
        $action = $request->get('action');
        $count = count($pageIds);

        switch ($action) {
            case 'publish':
                Page::whereIn('id', $pageIds)->update(['is_published' => true]);
                $message = "{$count}개 페이지가 발행되었습니다.";
                break;

            case 'unpublish':
                Page::whereIn('id', $pageIds)->update(['is_published' => false]);
                $message = "{$count}개 페이지가 비공개되었습니다.";
                break;

            case 'change_template':
                $template = $request->get('template');
                Page::whereIn('id', $pageIds)->update(['template' => $template]);
                $message = "{$count}개 페이지의 템플릿이 변경되었습니다.";
                break;

            case 'delete':
                // 하위 페이지가 있는 페이지는 삭제하지 않음
                $deletablePages = Page::whereIn('id', $pageIds)
                    ->whereDoesntHave('children')
                    ->get();
                
                $deletedCount = $deletablePages->count();
                Page::whereIn('id', $deletablePages->pluck('id'))->delete();
                
                if ($deletedCount < $count) {
                    $message = "{$deletedCount}개 페이지가 삭제되었습니다. (하위 페이지가 있는 " . ($count - $deletedCount) . "개 페이지는 삭제되지 않았습니다.)";
                } else {
                    $message = "{$deletedCount}개 페이지가 삭제되었습니다.";
                }
                break;

            default:
                return redirect()->route('admin.pages.index')
                    ->with('error', '알 수 없는 작업입니다.');
        }

        return redirect()->route('admin.pages.index')
            ->with('success', $message);
    }

    /**
     * 고유한 슬러그 보장
     */
    private function ensureUniqueSlug(string $slug, int $excludeId = null): string
    {
        $originalSlug = $slug;
        $counter = 1;

        $query = Page::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            
            $query = Page::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * 읽기 시간 계산
     */
    private function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        $readingSpeed = 200; // 분당 평균 단어 수
        return max(1, ceil($wordCount / $readingSpeed));
    }

    /**
     * 사용 가능한 템플릿 목록
     */
    private function getAvailableTemplates(): array
    {
        return [
            'default' => '기본 템플릿',
            'full-width' => '전체 폭',
            'landing' => '랜딩 페이지',
            'contact' => '연락처',
            'about' => '소개',
            'portfolio' => '포트폴리오',
            'blank' => '빈 페이지',
        ];
    }
}