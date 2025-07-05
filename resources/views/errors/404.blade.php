@extends('themes.default.layouts.app')

@section('title', '페이지를 찾을 수 없습니다')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="max-w-md mx-auto text-center">
        <div class="mb-8">
            <h1 class="text-9xl font-bold text-indigo-600">404</h1>
            <div class="text-2xl font-semibold text-gray-700 mb-4">페이지를 찾을 수 없습니다</div>
            <p class="text-gray-500 mb-8">
                요청하신 페이지를 찾을 수 없습니다.<br>
                URL을 다시 확인해주시거나 홈페이지로 돌아가세요.
            </p>
        </div>
        
        <div class="space-y-4">
            <a href="{{ route('home') }}" 
               class="inline-block px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition duration-200">
                홈으로 돌아가기
            </a>
            
            <div class="text-sm text-gray-500">
                또는 
                <a href="{{ route('posts.index') }}" class="text-indigo-600 hover:underline">
                    블로그 포스트 보기
                </a>
            </div>
        </div>
        
        <!-- 검색 기능 -->
        <div class="mt-8 p-6 bg-white rounded-lg shadow-sm">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">찾고 계신 내용이 있나요?</h3>
            <form action="{{ route('search.index') }}" method="GET" class="flex gap-2">
                <input type="text" 
                       name="q" 
                       placeholder="검색어를 입력하세요..." 
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <button type="submit" 
                        class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200">
                    검색
                </button>
            </form>
        </div>
    </div>
</div>
@endsection