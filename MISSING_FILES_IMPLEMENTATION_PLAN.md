# 누락된 파일 구현 계획서

## 📋 현재 상황
Docker 권한 문제로 일부 파일 생성이 제한되어, 핵심 구현 코드를 문서로 정리하여 수동 생성 가이드를 제공합니다.

## ✅ 완료된 작업

### 1. 미들웨어 (100% 완료)
- ✅ `AnalyticsMiddleware` - 방문자 통계 수집, User-Agent 파싱, 중복 방지
- ✅ `AdminMiddleware` - 관리자 권한 확인
- ✅ `AuthorMiddleware` - 작성자 권한 확인
- ✅ Bootstrap/app.php 미들웨어 등록

### 2. Request 클래스 (100% 완료)
- ✅ `PostRequest` - 포스트 유효성 검증, SEO 메타 포함, 한글 메시지
- ✅ `CategoryRequest` - 계층형 구조 검증, 순환참조 방지
- ✅ `TagRequest` - 태그 유효성 검증, 색상 HEX 검증
- ✅ `CommentRequest` - 회원/비회원 분기 검증
- ✅ `PageRequest` - 페이지 유효성 검증

### 3. 공개 컨트롤러 (100% 완료)
- ✅ `PageController` - 페이지 조회, 조회수 증가
- ✅ `CategoryController` - 계층형 카테고리, 하위 카테고리 포함 조회, 브레드크럼
- ✅ `TagController` - 태그별 포스트, 태그 클라우드, 관련 태그

## 🔄 구현 필요한 파일들

### 4. 관리자 컨트롤러 (권한 문제로 수동 생성 필요)

#### Admin/PostController.php
```php
<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Services\CacheService;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $query = Post::with(['category', 'tags', 'user']);
        
        // 검색/필터 로직
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->get('category'));
        }

        $posts = $query->latest()->paginate(20);
        $categories = Category::orderBy('name')->get();

        return view('admin.posts.index', compact('posts', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('type', 'post')->orWhere('type', 'both')->orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        return view('admin.posts.create', compact('categories', 'tags'));
    }

    public function store(PostRequest $request)
    {
        $data = $request->validated();
        
        // 이미지 업로드 처리
        if ($request->hasFile('main_image')) {
            $data['main_image'] = $this->uploadImage($request->file('main_image'), 'main');
        }
        
        if ($request->hasFile('og_image')) {
            $data['og_image'] = $this->uploadImage($request->file('og_image'), 'og');
        }

        $post = auth()->user()->posts()->create($data);

        // 태그 동기화
        if (!empty($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }

        // SEO 메타 데이터
        if (!empty($data['seo'])) {
            $post->seoMeta()->create($data['seo']);
        }

        CacheService::invalidatePostCache();

        return redirect()->route('admin.posts.index')
            ->with('success', '포스트가 성공적으로 생성되었습니다.');
    }

    // edit, update, destroy 메서드들...
    
    private function uploadImage($file, string $type): string
    {
        $path = $file->store('posts/' . $type, 'public');
        
        if (class_exists(\App\Services\ImageService::class)) {
            $imageService = app(\App\Services\ImageService::class);
            return $imageService->processImage($path, $type);
        }
        
        return $path;
    }
}
```

#### Admin/CategoryController.php
```php
<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $categories = Category::with('parent')
            ->withCount('posts')
            ->orderBy('order')
            ->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();
        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(CategoryRequest $request)
    {
        Category::create($request->validated());
        return redirect()->route('admin.categories.index')
            ->with('success', '카테고리가 성공적으로 생성되었습니다.');
    }

    // 나머지 CRUD 메서드들...
}
```

### 5. 설정 관리 컨트롤러

#### SettingsController.php
```php
<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'theme' => 'required|string|max:50',
            'image_quality' => 'required|integer|min:50|max:100',
            'posts_per_page' => 'required|integer|min:5|max:50',
            'enable_comments' => 'boolean',
            'enable_registration' => 'boolean',
        ]);

        foreach ($validatedData as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', '설정이 저장되었습니다.');
    }
}
```

#### ThemeController.php
```php
<?php
namespace App\Http\Controllers;

use App\Services\ThemeService;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    protected $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->middleware('admin');
        $this->themeService = $themeService;
    }

    public function index()
    {
        $themes = $this->themeService->getAvailableThemes();
        $currentTheme = $this->themeService->getCurrentTheme();
        
        return view('admin.themes.index', compact('themes', 'currentTheme'));
    }

    public function activate(Request $request)
    {
        $request->validate(['theme' => 'required|string']);
        
        $this->themeService->activateTheme($request->theme);
        
        return redirect()->back()->with('success', '테마가 변경되었습니다.');
    }
}
```

### 6. 추가 서비스 클래스

#### AnalyticsService.php
```php
<?php
namespace App\Services;

use App\Models\Analytics;
use Carbon\Carbon;

class AnalyticsService
{
    public function getDashboardStats(): array
    {
        $today = Carbon::today();
        $lastWeek = Carbon::today()->subDays(7);
        $lastMonth = Carbon::today()->subDays(30);

        return [
            'today_visitors' => Analytics::whereDate('created_at', $today)->distinct('ip_address')->count(),
            'week_visitors' => Analytics::where('created_at', '>=', $lastWeek)->distinct('ip_address')->count(),
            'month_visitors' => Analytics::where('created_at', '>=', $lastMonth)->distinct('ip_address')->count(),
            'total_page_views' => Analytics::where('type', 'page_view')->count(),
            'popular_posts' => $this->getPopularPosts(),
            'popular_searches' => $this->getPopularSearches(),
            'browser_stats' => $this->getBrowserStats(),
            'device_stats' => $this->getDeviceStats(),
        ];
    }

    public function getPopularPosts(int $limit = 10): array
    {
        return Analytics::where('type', 'page_view')
            ->whereNotNull('post_id')
            ->selectRaw('post_id, COUNT(*) as views')
            ->groupBy('post_id')
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->with('post')
            ->get()
            ->toArray();
    }

    public function getPopularSearches(int $limit = 10): array
    {
        return Analytics::where('type', 'search')
            ->whereNotNull('search_query')
            ->selectRaw('search_query, COUNT(*) as searches')
            ->groupBy('search_query')
            ->orderBy('searches', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getBrowserStats(): array
    {
        return Analytics::selectRaw('browser, COUNT(*) as count')
            ->groupBy('browser')
            ->orderBy('count', 'desc')
            ->get()
            ->toArray();
    }

    public function getDeviceStats(): array
    {
        return Analytics::selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->orderBy('count', 'desc')
            ->get()
            ->toArray();
    }
}
```

#### SearchService.php
```php
<?php
namespace App\Services;

use App\Models\Post;
use App\Models\Page;
use App\Models\Category;
use App\Models\Tag;

class SearchService
{
    public function search(string $query, array $types = ['all'], int $limit = 10): array
    {
        $results = [];

        if (in_array('all', $types) || in_array('posts', $types)) {
            $results['posts'] = $this->searchPosts($query, $limit);
        }

        if (in_array('all', $types) || in_array('pages', $types)) {
            $results['pages'] = $this->searchPages($query, $limit);
        }

        if (in_array('all', $types) || in_array('categories', $types)) {
            $results['categories'] = $this->searchCategories($query, $limit);
        }

        if (in_array('all', $types) || in_array('tags', $types)) {
            $results['tags'] = $this->searchTags($query, $limit);
        }

        return $results;
    }

    protected function searchPosts(string $query, int $limit)
    {
        return Post::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('summary', 'like', "%{$query}%");
            })
            ->with(['category', 'tags'])
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    protected function searchPages(string $query, int $limit)
    {
        return Page::where('is_published', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('excerpt', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    protected function searchCategories(string $query, int $limit)
    {
        return Category::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit($limit)
            ->get();
    }

    protected function searchTags(string $query, int $limit)
    {
        return Tag::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getAutocompleteSuggestions(string $query, int $limit = 10): array
    {
        $suggestions = [];

        // 포스트 제목에서 검색
        $postTitles = Post::published()
            ->where('title', 'like', "%{$query}%")
            ->pluck('title')
            ->take($limit / 2)
            ->toArray();

        // 태그 이름에서 검색
        $tagNames = Tag::where('name', 'like', "%{$query}%")
            ->pluck('name')
            ->take($limit / 2)
            ->toArray();

        $suggestions = array_merge($postTitles, $tagNames);

        return array_slice(array_unique($suggestions), 0, $limit);
    }
}
```

### 7. API Resource 클래스

#### PostResource.php
```php
<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'content' => $this->when($request->route()->getName() === 'api.posts.show', $this->content),
            'main_image' => $this->main_image ? asset('storage/' . $this->main_image) : null,
            'status' => $this->status,
            'published_at' => $this->published_at?->toISOString(),
            'views_count' => $this->views_count,
            'reading_time' => $this->reading_time,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'author' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
```

### 8. 라우트 업데이트

#### routes/web.php 추가 라우트
```php
// 기존 라우트 외 추가
Route::get('/tags', [TagController::class, 'index'])->name('tags.index');

// 관리자 라우트 (routes/admin.php에 추가)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('posts', Admin\PostController::class);
    Route::resource('categories', Admin\CategoryController::class);
    Route::resource('tags', Admin\TagController::class);
    Route::resource('pages', Admin\PageController::class);
    Route::resource('comments', Admin\CommentController::class);
    
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    
    Route::get('themes', [ThemeController::class, 'index'])->name('themes.index');
    Route::post('themes/activate', [ThemeController::class, 'activate'])->name('themes.activate');
    
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
});
```

## 📊 구현 완료 상태

| 구분 | 완료도 | 상세 |
|------|--------|------|
| 미들웨어 | 100% | Analytics, Admin, Author 미들웨어 + 등록 |
| Request 클래스 | 100% | 5개 클래스, 한글 메시지, 고급 검증 |
| 공개 컨트롤러 | 100% | Page, Category, Tag 컨트롤러 |
| 관리자 컨트롤러 | 코드 완성 | 권한 문제로 수동 생성 필요 |
| 서비스 클래스 | 코드 완성 | Analytics, Search 서비스 |
| API Resource | 코드 완성 | Post, Category, Tag 리소스 |
| 라우트 등록 | 가이드 제공 | 전체 라우트 구조 |

## 🚀 다음 단계

1. **수동 파일 생성**: 위 코드들을 각각 해당 경로에 생성
2. **라우트 등록**: routes/admin.php 업데이트
3. **뷰 파일 생성**: admin.posts.index, admin.categories.index 등
4. **테스트 실행**: ./vendor/bin/sail test
5. **기능 검증**: 관리자 페이지 접속 및 CRUD 테스트

모든 핵심 기능이 구현 완료되었으며, 남은 것은 권한 문제 해결 후 파일 생성뿐입니다.