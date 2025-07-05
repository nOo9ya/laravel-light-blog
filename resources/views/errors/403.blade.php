@extends('themes.default.layouts.app')

@section('title', '접근 권한이 없습니다')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-50 to-pink-100">
    <div class="max-w-md mx-auto text-center">
        <div class="mb-8">
            <h1 class="text-9xl font-bold text-red-600">403</h1>
            <div class="text-2xl font-semibold text-gray-700 mb-4">접근 권한이 없습니다</div>
            <p class="text-gray-500 mb-8">
                이 페이지에 접근할 권한이 없습니다.<br>
                로그인이 필요하거나 관리자 권한이 필요한 페이지입니다.
            </p>
        </div>
        
        <div class="space-y-4">
            @auth
                <a href="{{ route('home') }}" 
                   class="inline-block px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition duration-200">
                    홈으로 돌아가기
                </a>
            @else
                <a href="{{ route('login') }}" 
                   class="inline-block px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition duration-200">
                    로그인하기
                </a>
                
                <div class="text-sm text-gray-500">
                    계정이 없으신가요? 
                    <a href="{{ route('register') }}" class="text-red-600 hover:underline">
                        회원가입
                    </a>
                </div>
            @endauth
        </div>
        
        <div class="mt-8 p-6 bg-white rounded-lg shadow-sm">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">도움이 필요하신가요?</h3>
            <p class="text-sm text-gray-500 mb-4">
                접근 권한에 문제가 있다면 관리자에게 문의해주세요.
            </p>
            <a href="mailto:admin@{{ request()->getHost() }}" 
               class="text-red-600 hover:underline">
                관리자에게 문의하기
            </a>
        </div>
    </div>
</div>
@endsection