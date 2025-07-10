@extends('themes.default.layouts.app')

@section('title', '프로필 수정')
@section('meta_description', '개인 정보를 수정하고 계정 설정을 변경하세요.')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- 헤더 -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">프로필 수정</h1>
                <p class="text-gray-600 mt-2">개인 정보를 수정하고 계정 설정을 변경하세요</p>
            </div>
            <a href="{{ route('profile.show', auth()->user()) }}" 
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                프로필로 돌아가기
            </a>
        </div>

        <!-- 성공 메시지 -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- 프로필 정보 수정 -->
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- 기본 정보 -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">기본 정보</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 이름 -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            이름 <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}"
                               required
                               maxlength="255"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 이메일 -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            이메일 주소 <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if(!$user->email_verified_at)
                            <p class="mt-1 text-sm text-orange-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                이메일 인증이 필요합니다.
                                <a href="{{ route('verification.send') }}" class="ml-2 text-indigo-600 hover:text-indigo-800 underline">
                                    인증 이메일 재발송
                                </a>
                            </p>
                        @endif
                    </div>
                </div>

                <!-- 자기소개 -->
                <div class="mt-6">
                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">
                        자기소개 (선택사항)
                    </label>
                    <textarea id="bio" 
                              name="bio" 
                              rows="4" 
                              maxlength="500"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('bio') border-red-500 @enderror"
                              placeholder="자신을 간단히 소개해보세요...">{{ old('bio', $user->bio ?? '') }}</textarea>
                    @error('bio')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">최대 500자까지 입력할 수 있습니다.</p>
                </div>
            </div>

            <!-- 비밀번호 변경 -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">비밀번호 변경</h2>
                <p class="text-sm text-gray-600 mb-6">비밀번호를 변경하려면 아래 필드를 입력하세요. 변경하지 않으려면 비워두세요.</p>
                
                <div class="space-y-6">
                    <!-- 현재 비밀번호 -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                            현재 비밀번호
                        </label>
                        <input type="password" 
                               id="current_password" 
                               name="current_password" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('current_password') border-red-500 @enderror">
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- 새 비밀번호 -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                새 비밀번호
                            </label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('password') border-red-500 @enderror">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            
                            <!-- 비밀번호 강도 표시 -->
                            <div id="password-strength" class="mt-2 hidden">
                                <div class="flex items-center">
                                    <div class="flex-1">
                                        <div class="h-2 bg-gray-200 rounded-full">
                                            <div id="password-strength-bar" class="h-full rounded-full transition-all duration-300"></div>
                                        </div>
                                    </div>
                                    <span id="password-strength-text" class="ml-2 text-xs"></span>
                                </div>
                            </div>
                        </div>

                        <!-- 새 비밀번호 확인 -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                새 비밀번호 확인
                            </label>
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <div id="password-match" class="mt-1 text-sm hidden"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 계정 설정 -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">계정 설정</h2>
                
                <div class="space-y-6">
                    <!-- 알림 설정 -->
                    <div>
                        <h3 class="text-base font-medium text-gray-900 mb-3">알림 설정</h3>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="notifications[email_comments]" 
                                       value="1"
                                       {{ old('notifications.email_comments', $user->settings['notifications']['email_comments'] ?? true) ? 'checked' : '' }}
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                <span class="ml-3 text-sm text-gray-700">댓글 알림 이메일 받기</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="notifications[email_posts]" 
                                       value="1"
                                       {{ old('notifications.email_posts', $user->settings['notifications']['email_posts'] ?? false) ? 'checked' : '' }}
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                <span class="ml-3 text-sm text-gray-700">새 포스트 알림 이메일 받기</span>
                            </label>
                        </div>
                    </div>

                    <!-- 개인정보 설정 -->
                    <div>
                        <h3 class="text-base font-medium text-gray-900 mb-3">개인정보 설정</h3>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="privacy[show_email]" 
                                       value="1"
                                       {{ old('privacy.show_email', $user->settings['privacy']['show_email'] ?? false) ? 'checked' : '' }}
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                <span class="ml-3 text-sm text-gray-700">프로필에서 이메일 주소 공개</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="privacy[show_activity]" 
                                       value="1"
                                       {{ old('privacy.show_activity', $user->settings['privacy']['show_activity'] ?? true) ? 'checked' : '' }}
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                <span class="ml-3 text-sm text-gray-700">활동 내역 공개</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 계정 정보 -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">계정 정보</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-600">
                    <div>
                        <strong>가입일:</strong> {{ $user->created_at->format('Y년 m월 d일') }}
                    </div>
                    <div>
                        <strong>역할:</strong> 
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                            {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $user->role === 'admin' ? '관리자' : '작성자' }}
                        </span>
                    </div>
                    <div>
                        <strong>최근 로그인:</strong> {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : '정보 없음' }}
                    </div>
                    <div>
                        <strong>이메일 인증:</strong> 
                        <span class="{{ $user->email_verified_at ? 'text-green-600' : 'text-orange-600' }}">
                            {{ $user->email_verified_at ? '인증됨' : '미인증' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 저장 버튼 -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('profile.show', auth()->user()) }}" 
                   class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-200">
                    취소
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    저장하기
                </button>
            </div>
        </form>

        <!-- 계정 삭제 -->
        <div class="mt-12 bg-red-50 border border-red-200 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-red-900 mb-4">위험 영역</h2>
            <p class="text-sm text-red-700 mb-4">
                계정을 삭제하면 모든 데이터가 영구적으로 삭제되며 복구할 수 없습니다.
            </p>
            <button type="button" 
                    onclick="confirmDeleteAccount()"
                    class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition duration-200">
                계정 삭제
            </button>
        </div>
    </div>
</div>

@push('scripts')
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
    const strengthDiv = document.getElementById('password-strength');
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');
    
    if (password.length === 0) {
        strengthDiv.classList.add('hidden');
        return;
    }
    
    strengthDiv.classList.remove('hidden');
    
    const { score } = checkPasswordStrength(password);
    const levels = [
        { width: '20%', color: 'bg-red-500', text: '매우 약함', textColor: 'text-red-600' },
        { width: '40%', color: 'bg-orange-500', text: '약함', textColor: 'text-orange-600' },
        { width: '60%', color: 'bg-yellow-500', text: '보통', textColor: 'text-yellow-600' },
        { width: '80%', color: 'bg-blue-500', text: '강함', textColor: 'text-blue-600' },
        { width: '100%', color: 'bg-green-500', text: '매우 강함', textColor: 'text-green-600' }
    ];
    
    const level = levels[Math.max(0, score - 1)];
    strengthBar.style.width = level.width;
    strengthBar.className = `h-full rounded-full transition-all duration-300 ${level.color}`;
    strengthText.textContent = level.text;
    strengthText.className = `ml-2 text-xs ${level.textColor}`;
}

// 비밀번호 일치 확인
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    const matchDiv = document.getElementById('password-match');
    
    if (confirmation.length === 0) {
        matchDiv.classList.add('hidden');
    } else if (password === confirmation) {
        matchDiv.className = 'mt-1 text-sm text-green-600';
        matchDiv.textContent = '✓ 비밀번호가 일치합니다';
        matchDiv.classList.remove('hidden');
    } else {
        matchDiv.className = 'mt-1 text-sm text-red-600';
        matchDiv.textContent = '✗ 비밀번호가 일치하지 않습니다';
        matchDiv.classList.remove('hidden');
    }
}

// 계정 삭제 확인
function confirmDeleteAccount() {
    if (confirm('정말로 계정을 삭제하시겠습니까?\n\n이 작업은 되돌릴 수 없으며, 모든 데이터가 영구적으로 삭제됩니다.')) {
        if (confirm('마지막 확인입니다. 정말로 계정을 삭제하시겠습니까?')) {
            // 계정 삭제 폼 생성 및 제출
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("profile.destroy") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    }
}

// 이벤트 리스너
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    const confirmationField = document.getElementById('password_confirmation');
    
    passwordField.addEventListener('input', function() {
        updatePasswordStrength(this.value);
        checkPasswordMatch();
    });
    
    confirmationField.addEventListener('input', checkPasswordMatch);
});
</script>
@endpush
@endsection