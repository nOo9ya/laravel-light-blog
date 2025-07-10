<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationController extends Controller
{
    /**
     * 이메일 인증 안내 페이지 표시
     */
    public function notice(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('home', absolute: false))
                    : view('auth.verify-email');
    }

    /**
     * 이메일 인증 처리
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home', absolute: false));
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('home', absolute: false))
                        ->with('status', '이메일 인증이 완료되었습니다.');
    }

    /**
     * 이메일 인증 링크 재전송
     */
    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', '인증 이메일이 재전송되었습니다.');
    }

    /**
     * 인증 상태 확인 (AJAX)
     */
    public function status(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'verified' => $user ? $user->hasVerifiedEmail() : false,
            'email' => $user ? $user->email : null,
            'verification_sent_at' => $user && $user->email_verified_at 
                ? $user->email_verified_at->toISOString() 
                : null
        ]);
    }

    /**
     * 인증 이메일 재전송 (AJAX)
     */
    public function resend(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '로그인이 필요합니다.'
            ], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => '이미 인증된 이메일입니다.'
            ]);
        }

        // 재전송 제한 체크 (1분에 1번)
        $lastSent = session('verification_last_sent.' . $user->id);
        if ($lastSent && now()->diffInMinutes($lastSent) < 1) {
            return response()->json([
                'success' => false,
                'message' => '잠시 후 다시 시도해주세요. (1분 제한)'
            ]);
        }

        $user->sendEmailVerificationNotification();
        session(['verification_last_sent.' . $user->id => now()]);

        return response()->json([
            'success' => true,
            'message' => '인증 이메일이 재전송되었습니다.'
        ]);
    }

    /**
     * 이메일 변경 시 재인증 요청
     */
    public function requestReVerification(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'different:current_email'],
        ], [
            'email.required' => '새 이메일은 필수 입력 항목입니다.',
            'email.email' => '올바른 이메일 형식을 입력해주세요.',
            'email.different' => '현재 이메일과 다른 이메일을 입력해주세요.',
        ]);

        $user = $request->user();
        
        // 이메일 변경
        $user->update([
            'email' => $request->email,
            'email_verified_at' => null, // 인증 상태 초기화
        ]);

        // 새 이메일로 인증 링크 전송
        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')
                        ->with('status', '새 이메일로 인증 링크가 전송되었습니다.');
    }
}