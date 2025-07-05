@extends('themes.default.layouts.app')

@section('title', '새 비밀번호 설정')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                새 비밀번호 설정
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                새로운 비밀번호를 입력해주세요.<br>
                안전한 비밀번호로 설정하시길 권장합니다.
            </p>
        </div>

        <form class="mt-8 space-y-6" action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="space-y-4">
                <!-- 이메일 -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        이메일 주소
                    </label>
                    <input id="email" 
                           name="email" 
                           type="email" 
                           autocomplete="email" 
                           required 
                           readonly
                           value="{{ $email ?? old('email') }}"
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md bg-gray-50 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 새 비밀번호 -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        새 비밀번호 <span class="text-red-500">*</span>
                    </label>
                    <input id="password" 
                           name="password" 
                           type="password" 
                           autocomplete="new-password" 
                           required 
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('password') border-red-500 @enderror" 
                           placeholder="새로운 비밀번호를 입력하세요">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    
                    <!-- 비밀번호 강도 표시 -->
                    <div class="mt-2">
                        <div class="flex text-sm">
                            <div class="flex-1">
                                <div id="password-strength" class="h-2 bg-gray-200 rounded-full">
                                    <div id="password-strength-bar" class="h-full rounded-full transition-all duration-300"></div>
                                </div>
                            </div>
                            <span id="password-strength-text" class="ml-2 text-xs"></span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            • 최소 8자리 이상<br>
                            • 영문, 숫자, 특수문자 조합 권장
                        </p>
                    </div>
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
                    <div id="password-match" class="mt-1 text-sm hidden"></div>
                </div>
            </div>

            <div>
                <button type="submit" 
                        id="submit-btn"
                        disabled
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gray-400 cursor-not-allowed transition duration-200">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    비밀번호 재설정
                </button>
            </div>

            <div class="text-center">
                <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                    로그인 페이지로 돌아가기
                </a>
            </div>
        </form>

        <!-- 보안 안내 -->
        <div class="mt-8 bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">보안 팁</h3>
                    <div class="mt-2 text-sm text-green-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>다른 사이트와 다른 고유한 비밀번호 사용</li>
                            <li>정기적인 비밀번호 변경 권장</li>
                            <li>비밀번호는 안전한 곳에 보관</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 비밀번호 강도 검사
function checkPasswordStrength(password) {
    let score = 0;
    const checks = {
        length: password.length >= 8,
        lowercase: /[a-z]/.test(password),
        uppercase: /[A-Z]/.test(password),
        numbers: /\d/.test(password),
        symbols: /[^A-Za-z0-9]/.test(password)
    };

    score = Object.values(checks).filter(Boolean).length;
    
    return { score, checks };
}

// 비밀번호 강도 표시 업데이트
function updatePasswordStrength(password) {
    const { score } = checkPasswordStrength(password);
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');
    
    const levels = [
        { width: '20%', color: 'bg-red-500', text: '매우 약함', textColor: 'text-red-600' },
        { width: '40%', color: 'bg-orange-500', text: '약함', textColor: 'text-orange-600' },
        { width: '60%', color: 'bg-yellow-500', text: '보통', textColor: 'text-yellow-600' },
        { width: '80%', color: 'bg-blue-500', text: '강함', textColor: 'text-blue-600' },
        { width: '100%', color: 'bg-green-500', text: '매우 강함', textColor: 'text-green-600' }
    ];
    
    if (password.length === 0) {
        strengthBar.style.width = '0%';
        strengthBar.className = 'h-full rounded-full transition-all duration-300';
        strengthText.textContent = '';
        strengthText.className = 'ml-2 text-xs';
    } else {
        const level = levels[Math.max(0, score - 1)];
        strengthBar.style.width = level.width;
        strengthBar.className = `h-full rounded-full transition-all duration-300 ${level.color}`;
        strengthText.textContent = level.text;
        strengthText.className = `ml-2 text-xs ${level.textColor}`;
    }
}

// 비밀번호 일치 확인
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    const matchDiv = document.getElementById('password-match');
    const submitBtn = document.getElementById('submit-btn');
    
    if (confirmation.length === 0) {
        matchDiv.classList.add('hidden');
    } else if (password === confirmation) {
        matchDiv.className = 'mt-1 text-sm text-green-600';
        matchDiv.textContent = '✓ 비밀번호가 일치합니다';
        matchDiv.classList.remove('hidden');
        
        if (password.length >= 8) {
            submitBtn.disabled = false;
            submitBtn.className = 'group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200';
        }
    } else {
        matchDiv.className = 'mt-1 text-sm text-red-600';
        matchDiv.textContent = '✗ 비밀번호가 일치하지 않습니다';
        matchDiv.classList.remove('hidden');
        
        submitBtn.disabled = true;
        submitBtn.className = 'group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gray-400 cursor-not-allowed transition duration-200';
    }
}

// 이벤트 리스너
document.getElementById('password').addEventListener('input', function() {
    updatePasswordStrength(this.value);
    checkPasswordMatch();
});

document.getElementById('password_confirmation').addEventListener('input', checkPasswordMatch);
</script>
@endsection