@extends('themes.default.layouts.app')

@section('title', '시스템 설정')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- 헤더 -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">시스템 설정</h1>
            <p class="text-gray-600 mt-2">블로그의 전반적인 설정을 관리하세요</p>
        </div>

        <!-- 설정 탭 네비게이션 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button onclick="showTab('general')" 
                            id="tab-general"
                            class="tab-button py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap border-indigo-500 text-indigo-600">
                        일반 설정
                    </button>
                    <button onclick="showTab('blog')" 
                            id="tab-blog"
                            class="tab-button py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        블로그 설정
                    </button>
                    <button onclick="showTab('seo')" 
                            id="tab-seo"
                            class="tab-button py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        SEO 설정
                    </button>
                    <button onclick="showTab('email')" 
                            id="tab-email"
                            class="tab-button py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        이메일 설정
                    </button>
                </nav>
            </div>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- 일반 설정 탭 -->
            <div id="content-general" class="tab-content">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">기본 정보</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- 사이트 이름 -->
                        <div>
                            <label for="site_name" class="block text-sm font-medium text-gray-700 mb-2">
                                사이트 이름 <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="site_name" 
                                   name="settings[site_name]" 
                                   value="{{ old('settings.site_name', $settings['site_name'] ?? '') }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="블로그 이름을 입력하세요">
                            @error('settings.site_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 사이트 태그라인 -->
                        <div>
                            <label for="site_tagline" class="block text-sm font-medium text-gray-700 mb-2">
                                사이트 태그라인
                            </label>
                            <input type="text" 
                                   id="site_tagline" 
                                   name="settings[site_tagline]" 
                                   value="{{ old('settings.site_tagline', $settings['site_tagline'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="블로그 부제목">
                        </div>

                        <!-- 관리자 이메일 -->
                        <div>
                            <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-2">
                                관리자 이메일 <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   id="admin_email" 
                                   name="settings[admin_email]" 
                                   value="{{ old('settings.admin_email', $settings['admin_email'] ?? '') }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="admin@example.com">
                        </div>

                        <!-- 시간대 -->
                        <div>
                            <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">
                                시간대
                            </label>
                            <select id="timezone" 
                                    name="settings[timezone]" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="Asia/Seoul" {{ old('settings.timezone', $settings['timezone'] ?? 'Asia/Seoul') === 'Asia/Seoul' ? 'selected' : '' }}>
                                    아시아/서울 (KST)
                                </option>
                                <option value="UTC" {{ old('settings.timezone', $settings['timezone'] ?? '') === 'UTC' ? 'selected' : '' }}>
                                    UTC
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- 사이트 설명 -->
                    <div class="mt-6">
                        <label for="site_description" class="block text-sm font-medium text-gray-700 mb-2">
                            사이트 설명
                        </label>
                        <textarea id="site_description" 
                                  name="settings[site_description]" 
                                  rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="블로그에 대한 간단한 설명을 작성하세요...">{{ old('settings.site_description', $settings['site_description'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 블로그 설정 탭 -->
            <div id="content-blog" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">블로그 설정</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- 페이지당 포스트 수 -->
                        <div>
                            <label for="posts_per_page" class="block text-sm font-medium text-gray-700 mb-2">
                                페이지당 포스트 수
                            </label>
                            <input type="number" 
                                   id="posts_per_page" 
                                   name="settings[posts_per_page]" 
                                   value="{{ old('settings.posts_per_page', $settings['posts_per_page'] ?? 10) }}"
                                   min="1" 
                                   max="50"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <!-- 댓글 허용 여부 -->
                        <div>
                            <label for="comments_enabled" class="block text-sm font-medium text-gray-700 mb-2">
                                댓글 기능
                            </label>
                            <select id="comments_enabled" 
                                    name="settings[comments_enabled]" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="1" {{ old('settings.comments_enabled', $settings['comments_enabled'] ?? '1') === '1' ? 'selected' : '' }}>
                                    활성화
                                </option>
                                <option value="0" {{ old('settings.comments_enabled', $settings['comments_enabled'] ?? '1') === '0' ? 'selected' : '' }}>
                                    비활성화
                                </option>
                            </select>
                        </div>

                        <!-- 댓글 승인 필요 -->
                        <div>
                            <label for="comment_moderation" class="block text-sm font-medium text-gray-700 mb-2">
                                댓글 승인
                            </label>
                            <select id="comment_moderation" 
                                    name="settings[comment_moderation]" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="0" {{ old('settings.comment_moderation', $settings['comment_moderation'] ?? '0') === '0' ? 'selected' : '' }}>
                                    자동 승인
                                </option>
                                <option value="1" {{ old('settings.comment_moderation', $settings['comment_moderation'] ?? '0') === '1' ? 'selected' : '' }}>
                                    수동 승인 필요
                                </option>
                            </select>
                        </div>

                        <!-- 회원가입 허용 -->
                        <div>
                            <label for="registration_enabled" class="block text-sm font-medium text-gray-700 mb-2">
                                회원가입
                            </label>
                            <select id="registration_enabled" 
                                    name="settings[registration_enabled]" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="1" {{ old('settings.registration_enabled', $settings['registration_enabled'] ?? '1') === '1' ? 'selected' : '' }}>
                                    허용
                                </option>
                                <option value="0" {{ old('settings.registration_enabled', $settings['registration_enabled'] ?? '1') === '0' ? 'selected' : '' }}>
                                    비허용
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO 설정 탭 -->
            <div id="content-seo" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">SEO 설정</h2>
                    
                    <div class="space-y-6">
                        <!-- 메타 키워드 -->
                        <div>
                            <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">
                                메타 키워드
                            </label>
                            <input type="text" 
                                   id="meta_keywords" 
                                   name="settings[meta_keywords]" 
                                   value="{{ old('settings.meta_keywords', $settings['meta_keywords'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="키워드1, 키워드2, 키워드3">
                            <p class="mt-1 text-sm text-gray-500">쉼표로 구분하여 입력하세요.</p>
                        </div>

                        <!-- Open Graph 이미지 -->
                        <div>
                            <label for="og_image" class="block text-sm font-medium text-gray-700 mb-2">
                                Open Graph 기본 이미지
                            </label>
                            <input type="file" 
                                   id="og_image" 
                                   name="og_image" 
                                   accept="image/*"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="mt-1 text-sm text-gray-500">소셜 미디어 공유 시 사용될 기본 이미지입니다.</p>
                            
                            @if(isset($settings['og_image']) && $settings['og_image'])
                                <div class="mt-2">
                                    <img src="{{ asset($settings['og_image']) }}" 
                                         alt="현재 OG 이미지" 
                                         class="w-32 h-24 object-cover rounded border">
                                    <p class="text-sm text-gray-500 mt-1">현재 설정된 이미지</p>
                                </div>
                            @endif
                        </div>

                        <!-- Google Analytics -->
                        <div>
                            <label for="google_analytics" class="block text-sm font-medium text-gray-700 mb-2">
                                Google Analytics ID
                            </label>
                            <input type="text" 
                                   id="google_analytics" 
                                   name="settings[google_analytics]" 
                                   value="{{ old('settings.google_analytics', $settings['google_analytics'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="G-XXXXXXXXXX">
                        </div>

                        <!-- Google Search Console -->
                        <div>
                            <label for="google_site_verification" class="block text-sm font-medium text-gray-700 mb-2">
                                Google Site Verification
                            </label>
                            <input type="text" 
                                   id="google_site_verification" 
                                   name="settings[google_site_verification]" 
                                   value="{{ old('settings.google_site_verification', $settings['google_site_verification'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="verification code">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 이메일 설정 탭 -->
            <div id="content-email" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">이메일 설정</h2>
                    
                    <div class="space-y-6">
                        <!-- SMTP 설정 안내 -->
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">안내</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p>SMTP 설정은 .env 파일에서 관리됩니다. 이메일 기능을 사용하려면 다음 항목들을 설정해주세요:</p>
                                        <ul class="list-disc list-inside mt-2 space-y-1">
                                            <li>MAIL_MAILER=smtp</li>
                                            <li>MAIL_HOST=smtp.gmail.com</li>
                                            <li>MAIL_PORT=587</li>
                                            <li>MAIL_USERNAME=your-email@gmail.com</li>
                                            <li>MAIL_PASSWORD=your-app-password</li>
                                            <li>MAIL_ENCRYPTION=tls</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 발신자 이름 -->
                        <div>
                            <label for="mail_from_name" class="block text-sm font-medium text-gray-700 mb-2">
                                발신자 이름
                            </label>
                            <input type="text" 
                                   id="mail_from_name" 
                                   name="settings[mail_from_name]" 
                                   value="{{ old('settings.mail_from_name', $settings['mail_from_name'] ?? config('app.name')) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="블로그 이름">
                        </div>

                        <!-- 발신자 이메일 -->
                        <div>
                            <label for="mail_from_address" class="block text-sm font-medium text-gray-700 mb-2">
                                발신자 이메일
                            </label>
                            <input type="email" 
                                   id="mail_from_address" 
                                   name="settings[mail_from_address]" 
                                   value="{{ old('settings.mail_from_address', $settings['mail_from_address'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="noreply@example.com">
                        </div>

                        <!-- 이메일 테스트 -->
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">이메일 테스트</h3>
                            <div class="flex items-center space-x-4">
                                <input type="email" 
                                       id="test_email" 
                                       placeholder="테스트 이메일 주소"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <button type="button" 
                                        onclick="sendTestEmail()"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                                    테스트 발송
                                </button>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                이메일 설정이 올바른지 확인하기 위해 테스트 이메일을 발송할 수 있습니다.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 저장 버튼 -->
            <div class="flex justify-end space-x-4">
                <button type="button" 
                        onclick="resetForm()"
                        class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-200">
                    초기화
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    설정 저장
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// 탭 전환 기능
function showTab(tabName) {
    // 모든 탭 버튼 비활성화
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-indigo-500', 'text-indigo-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // 모든 탭 내용 숨기기
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // 선택된 탭 활성화
    document.getElementById(`tab-${tabName}`).classList.remove('border-transparent', 'text-gray-500');
    document.getElementById(`tab-${tabName}`).classList.add('border-indigo-500', 'text-indigo-600');
    
    // 선택된 탭 내용 보이기
    document.getElementById(`content-${tabName}`).classList.remove('hidden');
}

// 이메일 테스트 발송
function sendTestEmail() {
    const email = document.getElementById('test_email').value;
    if (!email) {
        alert('테스트 이메일 주소를 입력해주세요.');
        return;
    }
    
    // AJAX 요청 (실제 구현 시 추가)
    fetch('{{ route("admin.settings.test-email") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('테스트 이메일이 발송되었습니다.');
        } else {
            alert('이메일 발송에 실패했습니다: ' + data.message);
        }
    })
    .catch(error => {
        alert('오류가 발생했습니다.');
        console.error('Error:', error);
    });
}

// 폼 초기화
function resetForm() {
    if (confirm('모든 변경사항이 취소됩니다. 계속하시겠습니까?')) {
        location.reload();
    }
}

// 페이지 로드 시 URL 해시에 따라 탭 활성화
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash.substring(1);
    const validTabs = ['general', 'blog', 'seo', 'email'];
    
    if (validTabs.includes(hash)) {
        showTab(hash);
    } else {
        showTab('general');
    }
});
</script>
@endsection