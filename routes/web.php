<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('themes.default.home');
})->name('home');

// 인증 라우트 포함
require __DIR__.'/auth.php';

// 관리자 라우트 포함
require __DIR__.'/admin.php';

// 대시보드 리다이렉트
Route::get('/dashboard', function () {
    return redirect('/admin/dashboard');
})->middleware('auth');

// 공개 포스트 라우트
Route::get('/posts', [App\Http\Controllers\PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post}', [App\Http\Controllers\PostController::class, 'show'])->name('posts.show');

// 페이지 라우트
Route::get('/pages/{page}', [App\Http\Controllers\PageController::class, 'show'])->name('pages.show');

// 카테고리별 포스트
Route::get('/category/{category}', [App\Http\Controllers\CategoryController::class, 'show'])->name('categories.show');

// 태그별 포스트  
Route::get('/tag/{tag}', [App\Http\Controllers\TagController::class, 'show'])->name('tags.show');

// 검색 라우트
Route::get('/search', [App\Http\Controllers\SearchController::class, 'index'])->name('search.index');
Route::get('/api/search/autocomplete', [App\Http\Controllers\SearchController::class, 'autocomplete'])->name('search.autocomplete');

// 댓글 API 라우트
Route::prefix('api')->group(function () {
    Route::get('/posts/{post}/comments', [App\Http\Controllers\CommentController::class, 'index'])->name('api.comments.index');
    Route::post('/posts/{post}/comments', [App\Http\Controllers\CommentController::class, 'store'])->name('api.comments.store');
    Route::put('/comments/{comment}', [App\Http\Controllers\CommentController::class, 'update'])->name('api.comments.update');
    Route::delete('/comments/{comment}', [App\Http\Controllers\CommentController::class, 'destroy'])->name('api.comments.destroy');
});

// SEO 라우트
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [App\Http\Controllers\SitemapController::class, 'robots'])->name('robots');
