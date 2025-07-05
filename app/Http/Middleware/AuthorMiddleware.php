<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthorMiddleware
{
    /**
     * 작성자 권한 확인 미들웨어 (admin 또는 author 역할)
     * 
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 인증되지 않은 사용자는 로그인 페이지로 리다이렉트
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', '작성자 권한이 필요합니다. 로그인 후 이용해주세요.');
        }

        // admin 또는 author 권한이 없는 사용자는 홈페이지로 리다이렉트
        $user = Auth::user();
        if (!$user->hasRole('admin') && !$user->hasRole('author')) {
            return redirect()->route('home')
                ->with('error', '작성자 권한이 필요한 페이지입니다.');
        }

        return $next($request);
    }
}