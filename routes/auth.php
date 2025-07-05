<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 인증 관련 라우트
|--------------------------------------------------------------------------
|
| 여기에는 애플리케이션의 모든 인증 관련 라우트가 정의되어 있습니다.
| 이 라우트들은 guest 미들웨어로 보호되며, 로그인하지 않은 사용자만 접근 가능합니다.
|
*/

Route::middleware('guest')->group(function () {
    // 회원가입
    Route::get('register', [RegisterController::class, 'create'])
                ->name('register');

    Route::post('register', [RegisterController::class, 'store']);

    // 로그인
    Route::get('login', [LoginController::class, 'create'])
                ->name('login');

    Route::post('login', [LoginController::class, 'store']);

    // 비밀번호 재설정 요청
    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])
                ->name('password.request');

    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])
                ->name('password.email');

    // 비밀번호 재설정
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])
                ->name('password.reset');

    Route::post('reset-password', [ResetPasswordController::class, 'store'])
                ->name('password.store');
});

Route::middleware('auth')->group(function () {
    // 이메일 인증 안내
    Route::get('verify-email', [VerificationController::class, 'notice'])
                ->name('verification.notice');

    // 이메일 인증 처리
    Route::get('verify-email/{id}/{hash}', [VerificationController::class, 'verify'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    // 이메일 인증 재전송
    Route::post('email/verification-notification', [VerificationController::class, 'send'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    // 로그아웃
    Route::post('logout', [LoginController::class, 'destroy'])
                ->name('logout');
});

/*
|--------------------------------------------------------------------------
| AJAX API 라우트
|--------------------------------------------------------------------------
*/

// 중복 확인 (게스트용)
Route::middleware('guest')->group(function () {
    Route::post('api/auth/check-username', [RegisterController::class, 'checkUsername'])
                ->name('api.auth.check-username');
    
    Route::post('api/auth/check-email', [RegisterController::class, 'checkEmail'])
                ->name('api.auth.check-email');
    
    Route::post('api/auth/forgot-password/resend', [ForgotPasswordController::class, 'resend'])
                ->name('api.auth.forgot-password.resend');
    
    Route::post('api/auth/reset-password/validate-token', [ResetPasswordController::class, 'validateToken'])
                ->name('api.auth.reset-password.validate-token');
});

// 인증 상태 확인 (모든 사용자)
Route::get('api/auth/status', [LoginController::class, 'status'])
            ->name('api.auth.status');

// 인증된 사용자용 API
Route::middleware('auth')->group(function () {
    Route::get('api/auth/admin-check', [LoginController::class, 'adminCheck'])
                ->name('api.auth.admin-check');
    
    Route::post('api/auth/refresh-remember-token', [LoginController::class, 'refreshRememberToken'])
                ->name('api.auth.refresh-remember-token');
    
    Route::get('api/auth/verification-status', [VerificationController::class, 'status'])
                ->name('api.auth.verification-status');
    
    Route::post('api/auth/verification/resend', [VerificationController::class, 'resend'])
                ->middleware('throttle:6,1')
                ->name('api.auth.verification.resend');
    
    Route::post('api/auth/verification/request-re-verification', [VerificationController::class, 'requestReVerification'])
                ->name('api.auth.verification.request-re-verification');
});