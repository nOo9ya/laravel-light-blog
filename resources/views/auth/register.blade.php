@extends('themes.default.layouts.app')

@section('title', '회원가입')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                회원가입
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                이미 계정이 있으신가요?
                <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    로그인하기
                </a>
            </p>
        </div>
        
        <form class="mt-8 space-y-6" action="{{ route('register') }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                <!-- 이름 -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        이름 <span class="text-red-500">*</span>
                    </label>
                    <input id="name" 
                           name="name" 
                           type="text" 
                           autocomplete="name" 
                           required 
                           value="{{ old('name') }}"
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('name') border-red-500 @enderror" 
                           placeholder="홍길동">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 이메일 -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        이메일 주소 <span class="text-red-500">*</span>
                    </label>
                    <input id="email" 
                           name="email" 
                           type="email" 
                           autocomplete="email" 
                           required 
                           value="{{ old('email') }}"
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror" 
                           placeholder="user@example.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 비밀번호 -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        비밀번호 <span class="text-red-500">*</span>
                    </label>
                    <input id="password" 
                           name="password" 
                           type="password" 
                           autocomplete="new-password" 
                           required 
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('password') border-red-500 @enderror" 
                           placeholder="8자리 이상 입력해주세요">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        • 최소 8자리 이상<br>
                        • 영문, 숫자, 특수문자 조합 권장
                    </p>
                </div>

                <!-- 비밀번호 확인 -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                        비밀번호 확인 <span class="text-red-500">*</span>
                    </label>
                    <input id="password_confirmation" 
                           name="password_confirmation" 
                           type="password" 
                           autocomplete="new-password" 
                           required 
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="비밀번호를 다시 입력해주세요">
                </div>

                <!-- 역할 선택 (관리자만 변경 가능) -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">
                        역할
                    </label>
                    <select id="role" 
                            name="role" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="author" {{ old('role', 'author') == 'author' ? 'selected' : '' }}>
                            작성자 (Author)
                        </option>
                        @if(auth()->check() && auth()->user()->role === 'admin')
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>
                                관리자 (Admin)
                            </option>
                        @endif
                    </select>
                    <p class="mt-1 text-sm text-gray-500">
                        기본적으로 작성자 권한으로 가입됩니다.
                    </p>
                </div>
            </div>

            <!-- 약관 동의 -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="terms" 
                           name="terms" 
                           type="checkbox" 
                           required
                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded @error('terms') border-red-500 @enderror">
                </div>
                <div class="ml-3 text-sm">
                    <label for="terms" class="text-gray-700">
                        <span class="text-red-500">*</span> 
                        <a href="#" class="text-indigo-600 hover:text-indigo-500">이용약관</a> 및 
                        <a href="#" class="text-indigo-600 hover:text-indigo-500">개인정보처리방침</a>에 동의합니다.
                    </label>
                    @error('terms')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 제출 버튼 -->
            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    회원가입
                </button>
            </div>

            <!-- 소셜 로그인 (옵션) -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300" />
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-50 text-gray-500">또는</span>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-500">
                        빠른 회원가입을 원하신다면
                    </p>
                    <!-- 향후 소셜 로그인 버튼들을 여기에 추가 -->
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// 비밀번호 확인 실시간 검증
document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const passwordConfirm = this.value;
    
    if (passwordConfirm && password !== passwordConfirm) {
        this.setCustomValidity('비밀번호가 일치하지 않습니다.');
        this.classList.add('border-red-500');
    } else {
        this.setCustomValidity('');
        this.classList.remove('border-red-500');
    }
});
</script>
@endsection