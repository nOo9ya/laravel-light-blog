@extends('themes.default.layouts.app')

@section('title', '이메일 인증')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-yellow-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                이메일 인증이 필요합니다
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                계속하시려면 이메일 주소를 인증해주세요
            </p>
        </div>

        @if (session('resent'))
            <div class="rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            새로운 인증 링크가 이메일로 발송되었습니다!
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white shadow rounded-lg p-6">
            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    인증 이메일을 확인해주세요
                </h3>
                
                <div class="mb-4 text-sm text-gray-600">
                    <p class="mb-2">
                        <strong>{{ auth()->user()->email }}</strong> 주소로 
                        인증 이메일을 발송했습니다.
                    </p>
                    <p>
                        이메일에 포함된 링크를 클릭하여 인증을 완료해주세요.
                    </p>
                </div>

                <!-- 인증 이메일 재발송 -->
                <form class="inline" method="POST" action="{{ route('verification.resend') }}">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition duration-200">
                        <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        인증 이메일 다시 보내기
                    </button>
                </form>
            </div>
        </div>

        <!-- 안내사항 -->
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">확인사항</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>스팸 폴더도 확인해보세요</li>
                            <li>인증 링크는 24시간 동안 유효합니다</li>
                            <li>이메일이 오지 않으면 '다시 보내기'를 클릭하세요</li>
                            <li>인증 완료 후 자동으로 로그인됩니다</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- 이메일 변경 및 로그아웃 -->
        <div class="text-center space-y-3">
            <div class="text-sm text-gray-600">
                이메일 주소가 틀렸나요?
                <a href="{{ route('profile.edit') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    이메일 변경하기
                </a>
            </div>
            
            <div class="text-sm text-gray-600">
                다른 계정으로 로그인하시겠습니까?
                <form class="inline" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="font-medium text-indigo-600 hover:text-indigo-500">
                        로그아웃
                    </button>
                </form>
            </div>
        </div>

        <!-- 문제 해결 -->
        <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">문제가 있나요?</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>인증 과정에서 문제가 발생했다면 관리자에게 문의해주세요.</p>
                        <p class="mt-1">
                            <a href="mailto:admin@{{ request()->getHost() }}?subject=이메일 인증 문의&body=사용자 이메일: {{ auth()->user()->email }}" 
                               class="font-medium text-yellow-800 hover:text-yellow-900 underline">
                                관리자에게 문의하기
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 페이지 로드 시 인증 상태 확인
document.addEventListener('DOMContentLoaded', function() {
    // 5분마다 페이지 새로고침하여 인증 상태 확인
    setInterval(function() {
        // AJAX로 인증 상태 확인 후 인증되었으면 리다이렉트
        fetch('/email/verify-status', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.verified) {
                window.location.href = data.redirect || '/';
            }
        })
        .catch(error => {
            console.log('인증 상태 확인 중 오류:', error);
        });
    }, 300000); // 5분
});

// 이메일 재발송 버튼 클릭 제한
let lastResendTime = 0;
document.querySelector('form').addEventListener('submit', function(e) {
    const now = Date.now();
    if (now - lastResendTime < 60000) { // 1분 제한
        e.preventDefault();
        alert('1분 후에 다시 시도해주세요.');
        return false;
    }
    lastResendTime = now;
});
</script>
@endsection