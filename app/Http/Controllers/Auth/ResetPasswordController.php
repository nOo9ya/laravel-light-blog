<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    /**
     * 비밀번호 재설정 폼 표시
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * 새로운 비밀번호로 재설정
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'token.required' => '유효하지 않은 재설정 토큰입니다.',
            'email.required' => '이메일은 필수 입력 항목입니다.',
            'email.email' => '올바른 이메일 형식을 입력해주세요.',
            'password.required' => '새 비밀번호는 필수 입력 항목입니다.',
            'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
        ]);

        // 비밀번호 재설정 시도
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', '비밀번호가 성공적으로 재설정되었습니다.');
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
            Password::INVALID_TOKEN => '재설정 토큰이 유효하지 않습니다.',
            Password::RESET_THROTTLED => '너무 많은 재설정 시도가 있었습니다. 잠시 후 다시 시도해주세요.',
            default => '비밀번호 재설정에 실패했습니다. 다시 시도해주세요.'
        };
    }

    /**
     * 토큰 유효성 확인 (AJAX)
     */
    public function validateToken(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
        ]);

        $user = Password::getUser($request->only('email'));
        
        if (!$user) {
            return response()->json([
                'valid' => false,
                'message' => '해당 이메일로 등록된 사용자를 찾을 수 없습니다.'
            ]);
        }

        $valid = Password::tokenExists($user, $request->token);

        return response()->json([
            'valid' => $valid,
            'message' => $valid 
                ? '유효한 재설정 토큰입니다.' 
                : '재설정 토큰이 만료되었거나 유효하지 않습니다.'
        ]);
    }
}