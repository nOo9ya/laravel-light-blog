<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ThemeController;
use Illuminate\Support\Facades\Route;

// 관리자 대시보드
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // 설정 관리 (추후 구현)
    Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
    
    // 테마 관리 (추후 구현)
    Route::put('/theme', [ThemeController::class, 'update'])->name('admin.theme.update');
    
    // 사용자 관리 (추후 구현)
    Route::get('/users', function () {
        return view(themed('admin.users.index'));
    })->name('admin.users.index');
    
    // 포스트 관리
    Route::resource('posts', App\Http\Controllers\PostController::class)->names([
        'index' => 'admin.posts.index',
        'create' => 'admin.posts.create',
        'store' => 'admin.posts.store',
        'show' => 'admin.posts.show',
        'edit' => 'admin.posts.edit',
        'update' => 'admin.posts.update',
        'destroy' => 'admin.posts.destroy',
    ]);
    
    // 포스트 이미지 업로드 (AJAX)
    Route::post('/posts/upload-image', [App\Http\Controllers\PostController::class, 'uploadContentImage'])
        ->name('admin.posts.upload-image');
    
    // 댓글 관리
    Route::get('/comments', [App\Http\Controllers\CommentController::class, 'adminIndex'])->name('admin.comments.index');
    Route::post('/comments/{comment}/approve', [App\Http\Controllers\CommentController::class, 'approve'])->name('admin.comments.approve');
    Route::post('/comments/{comment}/spam', [App\Http\Controllers\CommentController::class, 'markAsSpam'])->name('admin.comments.spam');
    Route::delete('/comments/{comment}', [App\Http\Controllers\CommentController::class, 'destroy'])->name('admin.comments.destroy');
    Route::post('/comments/bulk-action', [App\Http\Controllers\CommentController::class, 'bulkAction'])->name('admin.comments.bulk-action');
    
    // 통계 관리
    Route::get('/analytics', function () {
        return view(themed('admin.analytics.index'));
    })->name('admin.analytics.index');
});