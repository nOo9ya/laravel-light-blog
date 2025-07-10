<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    /**
     * 비밀번호 재설정 링크 요청 폼 표시
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * 비밀번호 재설정 링크를 이메일로 전송
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => '이메일은 필수 입력 항목입니다.',
            'email.email' => '올바른 이메일 형식을 입력해주세요.',
        ]);

        // 비밀번호 재설정 링크 전송
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', '비밀번호 재설정 링크가 이메일로 전송되었습니다.');
        }

        return back()->withInput($request->only('email'))
                    ->withErrors(['email' => $this->getResetErrorMessage($status)]);
    }

    /**
     * 비밀번호 재설정 에러 메시지 반환
     */
    protected function getResetErrorMessage(string $status): string
    {
        return match($status) {
            Password::INVALID_USER => '해당 이메일로 등록된 사용자를 찾을 수 없습니다.',
            Password::RESET_THROTTLED => '너무 많은 재설정 요청이 있었습니다. 잠시 후 다시 시도해주세요.',
            default => '비밀번호 재설정 링크 전송에 실패했습니다. 다시 시도해주세요.'
        };
    }

    /**
     * 비밀번호 재설정 링크 재전송 (AJAX)
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return response()->json([
            'success' => $status == Password::RESET_LINK_SENT,
            'message' => $status == Password::RESET_LINK_SENT 
                ? '비밀번호 재설정 링크가 재전송되었습니다.'
                : $this->getResetErrorMessage($status)
        ]);
    }
}