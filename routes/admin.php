<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

// 관리자 대시보드 - admin 미들웨어 적용
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // 대시보드
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // 포스트 관리
    Route::resource('posts', AdminPostController::class);
    Route::post('/posts/upload-image', [AdminPostController::class, 'uploadImage'])->name('posts.upload-image');
    Route::post('/posts/{post}/duplicate', [AdminPostController::class, 'duplicate'])->name('posts.duplicate');
    Route::post('/posts/bulk-action', [AdminPostController::class, 'bulkAction'])->name('posts.bulk-action');
    
    // 카테고리 관리
    Route::resource('categories', AdminCategoryController::class);
    Route::post('/categories/{category}/move', [AdminCategoryController::class, 'move'])->name('categories.move');
    Route::post('/categories/reorder', [AdminCategoryController::class, 'reorder'])->name('categories.reorder');
    Route::post('/categories/bulk-action', [AdminCategoryController::class, 'bulkAction'])->name('categories.bulk-action');
    
    // 태그 관리
    Route::resource('tags', AdminTagController::class);
    Route::post('/tags/merge', [AdminTagController::class, 'merge'])->name('tags.merge');
    Route::post('/tags/bulk-action', [AdminTagController::class, 'bulkAction'])->name('tags.bulk-action');
    Route::get('/tags/search', [AdminTagController::class, 'search'])->name('tags.search');
    
    // 페이지 관리
    Route::resource('pages', AdminPageController::class);
    Route::post('/pages/{page}/duplicate', [AdminPageController::class, 'duplicate'])->name('pages.duplicate');
    Route::post('/pages/reorder', [AdminPageController::class, 'reorder'])->name('pages.reorder');
    Route::post('/pages/bulk-action', [AdminPageController::class, 'bulkAction'])->name('pages.bulk-action');
    
    // 댓글 관리
    Route::get('/comments', [AdminCommentController::class, 'index'])->name('comments.index');
    Route::get('/comments/{comment}', [AdminCommentController::class, 'show'])->name('comments.show');
    Route::put('/comments/{comment}', [AdminCommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [AdminCommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{comment}/approve', [AdminCommentController::class, 'approve'])->name('comments.approve');
    Route::post('/comments/{comment}/reject', [AdminCommentController::class, 'reject'])->name('comments.reject');
    Route::post('/comments/{comment}/spam', [AdminCommentController::class, 'markAsSpam'])->name('comments.spam');
    Route::post('/comments/bulk-action', [AdminCommentController::class, 'bulkAction'])->name('comments.bulk-action');
    Route::get('/comments/export', [AdminCommentController::class, 'export'])->name('comments.export');
    
    // 통계 관리
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics.index');
    Route::get('/analytics/export', [DashboardController::class, 'exportAnalytics'])->name('analytics.export');
    Route::get('/analytics/real-time', [DashboardController::class, 'realTimeAnalytics'])->name('analytics.real-time');
    
    // 에러 로그 관리
    Route::get('/logs', [DashboardController::class, 'errorLogs'])->name('logs.index');
    Route::get('/logs/ajax', [DashboardController::class, 'errorLogsAjax'])->name('logs.ajax');
    Route::delete('/logs/clear', [DashboardController::class, 'clearErrorLogs'])->name('logs.clear');
    
    // 알림 설정
    Route::get('/settings/notifications', [DashboardController::class, 'notificationSettings'])->name('settings.notifications');
    Route::put('/settings/notifications', [DashboardController::class, 'updateNotificationSettings'])->name('settings.notifications.update');
    Route::post('/settings/notifications/test', [DashboardController::class, 'sendTestNotification'])->name('settings.notifications.test');
    
    // 사용자 관리 (추후 구현)
    Route::get('/users', function () {
        return view('admin.users.index');
    })->name('users.index');
    
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

// 작성자 권한이 필요한 라우트 - author 미들웨어 적용  
Route::middleware(['auth', 'author'])->prefix('admin')->name('admin.')->group(function () {
    
    // 작성자는 자신의 포스트만 관리 가능
    Route::get('/my-posts', [AdminPostController::class, 'myPosts'])->name('posts.my');
    Route::get('/my-comments', [AdminCommentController::class, 'myComments'])->name('comments.my');
    
});