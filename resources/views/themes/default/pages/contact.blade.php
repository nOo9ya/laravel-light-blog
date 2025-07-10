@extends('themes.default.layouts.app')

@section('title', '연락처')
@section('meta_description', '궁금한 점이나 제안사항이 있으시면 언제든지 연락해주세요. 빠른 시간 내에 답변드리겠습니다.')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- 헤더 섹션 -->
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">연락처</h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            궁금한 점이나 제안사항이 있으시면 언제든지 연락해주세요. 
            빠른 시간 내에 답변드리겠습니다.
        </p>
    </div>

    <div class="max-w-6xl mx-auto">
        <div class="grid lg:grid-cols-2 gap-12">
            <!-- 연락처 정보 -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">연락처 정보</h2>
                
                <!-- 연락 방법들 -->
                <div class="space-y-6 mb-8">
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">이메일</h3>
                            <p class="text-gray-600 mb-2">가장 빠른 연락 방법입니다</p>
                            <a href="mailto:{{ config('mail.from.address', 'contact@example.com') }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium">
                                {{ config('mail.from.address', 'contact@example.com') }}
                            </a>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">소셜 미디어</h3>
                            <p class="text-gray-600 mb-2">SNS를 통해서도 소통하고 있습니다</p>
                            <div class="flex space-x-4">
                                <a href="#" class="text-gray-400 hover:text-blue-500 transition duration-200">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                    </svg>
                                </a>
                                <a href="#" class="text-gray-400 hover:text-blue-600 transition duration-200">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                                    </svg>
                                </a>
                                <a href="#" class="text-gray-400 hover:text-purple-500 transition duration-200">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.097.118.112.222.083.343-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.758-1.378l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.624 0 11.99-5.367 11.99-11.987C24.007 5.367 18.641.001 12.017.001z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">응답 시간</h3>
                            <p class="text-gray-600 mb-2">평균 응답 시간을 안내드립니다</p>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• 이메일: 24시간 이내</li>
                                <li>• 소셜 미디어: 12시간 이내</li>
                                <li>• 긴급 문의: 6시간 이내</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- FAQ 섹션 -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">자주 묻는 질문</h3>
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-1">게스트 포스팅이 가능한가요?</h4>
                            <p class="text-sm text-gray-600">
                                네, 가능합니다. 품질 기준을 만족하는 콘텐츠라면 게스트 포스팅을 환영합니다.
                            </p>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-1">광고나 협업 문의는 어떻게 하나요?</h4>
                            <p class="text-sm text-gray-600">
                                이메일로 구체적인 제안서와 함께 연락주시면 검토 후 답변드리겠습니다.
                            </p>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-1">기술적 오류를 발견했어요.</h4>
                            <p class="text-sm text-gray-600">
                                빠른 수정을 위해 상세한 오류 내용과 함께 이메일로 신고해주세요.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 연락 폼 -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">메시지 보내기</h2>
                
                <form action="{{ route('contact.send') }}" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    @csrf
                    
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

                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <!-- 이름 -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                이름 <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                                   placeholder="홍길동">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 이메일 -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                이메일 <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-500 @enderror"
                                   placeholder="user@example.com">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- 제목 -->
                    <div class="mb-6">
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                            제목 <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="subject" 
                               name="subject" 
                               value="{{ old('subject') }}"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('subject') border-red-500 @enderror"
                               placeholder="문의 제목을 입력해주세요">
                        @error('subject')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 문의 유형 -->
                    <div class="mb-6">
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            문의 유형
                        </label>
                        <select id="type" 
                                name="type" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">선택해주세요</option>
                            <option value="general" {{ old('type') === 'general' ? 'selected' : '' }}>일반 문의</option>
                            <option value="technical" {{ old('type') === 'technical' ? 'selected' : '' }}>기술적 문제</option>
                            <option value="collaboration" {{ old('type') === 'collaboration' ? 'selected' : '' }}>협업 제안</option>
                            <option value="content" {{ old('type') === 'content' ? 'selected' : '' }}>콘텐츠 관련</option>
                            <option value="other" {{ old('type') === 'other' ? 'selected' : '' }}>기타</option>
                        </select>
                    </div>

                    <!-- 메시지 -->
                    <div class="mb-6">
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                            메시지 <span class="text-red-500">*</span>
                        </label>
                        <textarea id="message" 
                                  name="message" 
                                  rows="6" 
                                  required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('message') border-red-500 @enderror"
                                  placeholder="궁금한 점이나 하고 싶은 말씀을 자세히 적어주세요...">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">최소 10자 이상 입력해주세요.</p>
                    </div>

                    <!-- 개인정보 동의 -->
                    <div class="mb-6">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="privacy" 
                                       name="privacy" 
                                       type="checkbox" 
                                       required
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="privacy" class="text-gray-700">
                                    <span class="text-red-500">*</span> 
                                    개인정보 수집 및 이용에 동의합니다.
                                    <a href="#" class="text-indigo-600 hover:text-indigo-500 ml-1">
                                        (자세히 보기)
                                    </a>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 제출 버튼 -->
                    <div class="text-center">
                        <button type="submit" 
                                class="w-full md:w-auto px-8 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                            메시지 보내기
                        </button>
                    </div>
                </form>

                <!-- 추가 안내 -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">안내사항</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>모든 문의는 24시간 이내에 답변드립니다.</li>
                                    <li>기술적 문제의 경우 상세한 설명을 포함해주세요.</li>
                                    <li>스팸이나 광고성 메시지는 자동으로 차단됩니다.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// 폼 유효성 검사
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const messageTextarea = document.getElementById('message');
    
    // 메시지 길이 검사
    messageTextarea.addEventListener('input', function() {
        const minLength = 10;
        const currentLength = this.value.length;
        
        if (currentLength < minLength) {
            this.setCustomValidity(`최소 ${minLength}자 이상 입력해주세요. (현재: ${currentLength}자)`);
        } else {
            this.setCustomValidity('');
        }
    });
    
    // 폼 제출 시 최종 검사
    form.addEventListener('submit', function(e) {
        const privacy = document.getElementById('privacy');
        const message = document.getElementById('message');
        
        if (!privacy.checked) {
            e.preventDefault();
            alert('개인정보 수집 및 이용에 동의해주세요.');
            privacy.focus();
            return false;
        }
        
        if (message.value.length < 10) {
            e.preventDefault();
            alert('메시지는 최소 10자 이상 입력해주세요.');
            message.focus();
            return false;
        }
    });
});
</script>
@endpush
@endsection