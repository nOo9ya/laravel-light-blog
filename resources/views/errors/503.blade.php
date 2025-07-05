@extends('themes.default.layouts.app')

@section('title', '서비스 점검 중입니다')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-50 to-blue-100">
    <div class="max-w-md mx-auto text-center">
        <div class="mb-8">
            <h1 class="text-9xl font-bold text-purple-600">503</h1>
            <div class="text-2xl font-semibold text-gray-700 mb-4">서비스 점검 중입니다</div>
            <p class="text-gray-500 mb-8">
                현재 시스템 점검 중입니다.<br>
                빠른 시일 내에 서비스를 재개하겠습니다.
            </p>
        </div>
        
        <div class="space-y-4">
            <button onclick="location.reload()" 
                    class="inline-block px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition duration-200">
                다시 시도하기
            </button>
        </div>
        
        <div class="mt-8 p-6 bg-white rounded-lg shadow-sm">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">점검 안내</h3>
            <div class="text-sm text-gray-500 space-y-2">
                <p>• 예상 점검 시간: 약 30분</p>
                <p>• 점검 내용: 시스템 업데이트 및 성능 개선</p>
                <p>• 문의: admin@{{ request()->getHost() }}</p>
            </div>
        </div>
        
        <div class="mt-6 text-xs text-gray-400">
            점검 시작: {{ now()->format('Y-m-d H:i') }}
        </div>
    </div>
</div>

<script>
// 10분마다 자동으로 페이지 새로고침
setTimeout(function() {
    location.reload();
}, 600000);
</script>
@endsection