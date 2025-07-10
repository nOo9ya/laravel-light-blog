<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * 로그인 폼 표시
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * 로그인 처리
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * 로그아웃 처리
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * 로그인 상태 확인 (AJAX)
     */
    public function status(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'authenticated' => !!$user,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'verified' => $user->hasVerifiedEmail(),
            ] : null
        ]);
    }

    /**
     * 관리자 로그인 체크
     */
    public function adminCheck(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'authorized' => false,
                'message' => '관리자 권한이 필요합니다.'
            ], 403);
        }

        return response()->json([
            'authorized' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * Remember Me 토큰 갱신
     */
    public function refreshRememberToken(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        if ($user) {
            $user->setRememberToken(\Illuminate\Support\Str::random(60));
            $user->save();
        }

        return back()->with('status', 'Remember Me 토큰이 갱신되었습니다.');
    }
}