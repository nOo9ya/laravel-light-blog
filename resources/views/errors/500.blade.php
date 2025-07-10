@extends('themes.default.layouts.app')

@section('title', '서버 오류가 발생했습니다')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-orange-50 to-red-100">
    <div class="max-w-md mx-auto text-center">
        <div class="mb-8">
            <h1 class="text-9xl font-bold text-orange-600">500</h1>
            <div class="text-2xl font-semibold text-gray-700 mb-4">서버 오류가 발생했습니다</div>
            <p class="text-gray-500 mb-8">
                죄송합니다. 서버에서 오류가 발생했습니다.<br>
                잠시 후 다시 시도해주세요.
            </p>
        </div>
        
        <div class="space-y-4">
            <button onclick="location.reload()" 
                    class="inline-block px-6 py-3 bg-orange-600 text-white font-semibold rounded-lg hover:bg-orange-700 transition duration-200">
                페이지 새로고침
            </button>
            
            <div class="text-sm text-gray-500">
                또는 
                <a href="{{ route('home') }}" class="text-orange-600 hover:underline">
                    홈으로 돌아가기
                </a>
            </div>
        </div>
        
        <div class="mt-8 p-6 bg-white rounded-lg shadow-sm">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">문제가 지속되나요?</h3>
            <p class="text-sm text-gray-500 mb-4">
                오류가 계속 발생한다면 관리자에게 신고해주세요.
            </p>
            
            @if(config('app.debug') && isset($exception))
            <details class="mt-4 text-left">
                <summary class="cursor-pointer text-sm font-medium text-orange-600 hover:text-orange-700">
                    개발자 정보 (디버그 모드)
                </summary>
                <div class="mt-2 p-3 bg-gray-100 rounded text-xs font-mono overflow-auto max-h-32">
                    <div><strong>파일:</strong> {{ $exception->getFile() }}</div>
                    <div><strong>라인:</strong> {{ $exception->getLine() }}</div>
                    <div><strong>메시지:</strong> {{ $exception->getMessage() }}</div>
                </div>
            </details>
            @endif
            
            <div class="mt-4">
                <a href="mailto:admin@{{ request()->getHost() }}?subject=서버 오류 신고&body=URL: {{ request()->fullUrl() }}%0A시간: {{ now() }}" 
                   class="text-orange-600 hover:underline">
                    오류 신고하기
                </a>
            </div>
        </div>
    </div>
</div>
@endsection