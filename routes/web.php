<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ThemeController;

// Analytics 미들웨어를 모든 라우트에 적용
Route::middleware('analytics')->group(function () {
    
    // 홈페이지
    Route::get('/', [PostController::class, 'index'])->name('home');

    // 공개 포스트 라우트
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');

    // 페이지 라우트
    Route::get('/pages/{page:slug}', [PageController::class, 'show'])->name('pages.show');
    Route::get('/pages', [PageController::class, 'index'])->name('pages.index');

    // 카테고리별 포스트
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/categories/{category:slug}/posts', [CategoryController::class, 'posts'])->name('categories.posts');

    // 태그별 포스트  
    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::get('/tags/{tag:slug}', [TagController::class, 'show'])->name('tags.show');
    Route::get('/tags/{tag:slug}/posts', [TagController::class, 'posts'])->name('tags.posts');

    // 검색 라우트
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
    Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');

    // 댓글 관련 라우트
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::get('/comments/{comment}/reply', [CommentController::class, 'reply'])->name('comments.reply');
    Route::post('/comments/{comment}/report', [CommentController::class, 'report'])->name('comments.report');
    
    // 인증된 사용자만 접근 가능한 댓글 기능
    Route::middleware('auth')->group(function () {
        Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    });

    // API 라우트
    Route::prefix('api')->name('api.')->group(function () {
        // 포스트 API
        Route::get('/posts', [PostController::class, 'apiIndex'])->name('posts.index');
        Route::get('/posts/{post:slug}', [PostController::class, 'apiShow'])->name('posts.show');
        Route::get('/posts/{post}/related', [PostController::class, 'related'])->name('posts.related');

        // 카테고리 API
        Route::get('/categories', [CategoryController::class, 'apiIndex'])->name('categories.index');
        Route::get('/categories/{category:slug}', [CategoryController::class, 'apiShow'])->name('categories.show');
        Route::get('/categories/{category}/children', [CategoryController::class, 'children'])->name('categories.children');
        Route::get('/categories/{category}/recent-posts', [CategoryController::class, 'recentPosts'])->name('categories.recent-posts');

        // 태그 API
        Route::get('/tags', [TagController::class, 'apiIndex'])->name('tags.index');
        Route::get('/tags/{tag:slug}', [TagController::class, 'apiShow'])->name('tags.show');
        Route::get('/tags/{tag}/similar', [TagController::class, 'similar'])->name('tags.similar');
        Route::get('/tags/{tag}/trending-posts', [TagController::class, 'trendingPosts'])->name('tags.trending-posts');

        // 페이지 API
        Route::get('/pages', [PageController::class, 'apiIndex'])->name('pages.index');
        Route::get('/pages/{page:slug}', [PageController::class, 'apiShow'])->name('pages.show');
        Route::get('/pages/{page}/siblings', [PageController::class, 'siblings'])->name('pages.siblings');
        Route::get('/pages/{page}/children', [PageController::class, 'children'])->name('pages.children');
        Route::get('/pages/{page}/related', [PageController::class, 'related'])->name('pages.related');

        // 댓글 API
        Route::get('/posts/{post}/comments', [CommentController::class, 'apiIndex'])->name('comments.index');
        Route::get('/comments/{comment}/thread', [CommentController::class, 'thread'])->name('comments.thread');
        Route::get('/comments/{comment}/siblings', [CommentController::class, 'siblings'])->name('comments.siblings');

        // 검색 API
        Route::get('/search', [SearchController::class, 'apiSearch'])->name('search');
        Route::get('/search/popular', [SearchController::class, 'popular'])->name('search.popular');
        Route::get('/search/recent', [SearchController::class, 'recent'])->name('search.recent');
    });

    // SEO 라우트
    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
    Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
    Route::get('/feed', [SitemapController::class, 'feed'])->name('feed');
    Route::get('/feed.rss', [SitemapController::class, 'rss'])->name('feed.rss');

});

// 관리자 전용 설정 라우트 (Analytics 미들웨어 제외)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // 설정 관리
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/reset', [SettingsController::class, 'reset'])->name('settings.reset');
    Route::post('/settings/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');

    // 테마 관리
    Route::get('/themes', [ThemeController::class, 'index'])->name('themes.index');
    Route::post('/themes/activate', [ThemeController::class, 'activate'])->name('themes.activate');
    Route::get('/themes/preview', [ThemeController::class, 'preview'])->name('themes.preview');
    Route::get('/themes/settings', [ThemeController::class, 'settings'])->name('themes.settings');
    Route::put('/themes/settings', [ThemeController::class, 'updateSettings'])->name('themes.update-settings');
    Route::post('/themes/clear-cache', [ThemeController::class, 'clearCache'])->name('themes.clear-cache');
});

// 인증 라우트 포함
require __DIR__.'/auth.php';

// 관리자 라우트 포함
require __DIR__.'/admin.php';

// 대시보드 리다이렉트
Route::get('/dashboard', function () {
    return redirect('/admin/dashboard');
})->middleware('auth');
