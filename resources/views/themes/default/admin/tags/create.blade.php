@extends('themes.default.layouts.app')

@section('title', '새 태그 생성')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- 헤더 -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">새 태그 생성</h1>
                <p class="text-gray-600 mt-2">블로그의 새로운 태그를 생성하세요</p>
            </div>
            <a href="{{ route('admin.tags.index') }}" 
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                목록으로 돌아가기
            </a>
        </div>

        <form action="{{ route('admin.tags.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">기본 정보</h2>
                
                <div class="space-y-6">
                    <!-- 태그 이름 -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            태그 이름 <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}"
                               required
                               maxlength="255"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                               placeholder="예: Laravel, PHP, 웹개발, 튜토리얼 등">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 슬러그 -->
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                            슬러그 (URL) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="slug" 
                               name="slug" 
                               value="{{ old('slug') }}"
                               required
                               maxlength="255"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('slug') border-red-500 @enderror"
                               placeholder="예: laravel, php, web-development 등">
                        <p class="mt-1 text-sm text-gray-500">
                            URL에 사용될 고유한 식별자입니다. 영문, 숫자, 하이픈만 사용 가능합니다.
                        </p>
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 색상 -->
                    <div>
                        <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                            태그 색상
                        </label>
                        <div class="flex items-center space-x-4">
                            <input type="color" 
                                   id="color" 
                                   name="color" 
                                   value="{{ old('color', '#3b82f6') }}"
                                   class="w-12 h-10 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            
                            <div class="flex space-x-2">
                                <button type="button" 
                                        onclick="setColor('#3b82f6')"
                                        class="w-8 h-8 rounded-full bg-blue-500 border-2 border-gray-300 hover:border-gray-400 transition duration-200"
                                        title="파란색"></button>
                                <button type="button" 
                                        onclick="setColor('#ef4444')"
                                        class="w-8 h-8 rounded-full bg-red-500 border-2 border-gray-300 hover:border-gray-400 transition duration-200"
                                        title="빨간색"></button>
                                <button type="button" 
                                        onclick="setColor('#10b981')"
                                        class="w-8 h-8 rounded-full bg-green-500 border-2 border-gray-300 hover:border-gray-400 transition duration-200"
                                        title="초록색"></button>
                                <button type="button" 
                                        onclick="setColor('#f59e0b')"
                                        class="w-8 h-8 rounded-full bg-yellow-500 border-2 border-gray-300 hover:border-gray-400 transition duration-200"
                                        title="노란색"></button>
                                <button type="button" 
                                        onclick="setColor('#8b5cf6')"
                                        class="w-8 h-8 rounded-full bg-purple-500 border-2 border-gray-300 hover:border-gray-400 transition duration-200"
                                        title="보라색"></button>
                                <button type="button" 
                                        onclick="setColor('#6b7280')"
                                        class="w-8 h-8 rounded-full bg-gray-500 border-2 border-gray-300 hover:border-gray-400 transition duration-200"
                                        title="회색"></button>
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            태그 표시 시 사용될 색상을 선택하세요.
                        </p>
                        @error('color')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 설명 -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            설명 (선택사항)
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="3" 
                                  maxlength="1000"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror"
                                  placeholder="이 태그에 대한 간단한 설명을 작성하세요...">{{ old('description') }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">
                            태그 페이지나 관리 화면에서 표시될 설명입니다. (최대 1000자)
                        </p>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- 미리보기 -->
            <div id="preview" class="bg-blue-50 border border-blue-200 rounded-lg p-6 hidden">
                <h2 class="text-lg font-semibold text-blue-900 mb-4">미리보기</h2>
                <div class="bg-white rounded-md p-4 border border-blue-200">
                    <div class="flex items-center mb-2">
                        <span id="preview-color" class="w-3 h-3 rounded-full mr-2"></span>
                        <span id="preview-name" class="text-lg font-medium text-gray-900"></span>
                    </div>
                    <div id="preview-slug" class="text-sm text-gray-500 mb-2"></div>
                    <div id="preview-description" class="text-sm text-gray-600"></div>
                </div>
            </div>

            <!-- 액션 버튼 -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.tags.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-200">
                    취소
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    태그 생성
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// 색상 설정 함수
function setColor(color) {
    document.getElementById('color').value = color;
    updatePreview();
}

// 실시간 미리보기
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const colorInput = document.getElementById('color');
    const descriptionInput = document.getElementById('description');
    const preview = document.getElementById('preview');

    // 슬러그 자동 생성
    nameInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.dataset.autoGenerated) {
            const slug = generateSlug(this.value);
            slugInput.value = slug;
            slugInput.dataset.autoGenerated = 'true';
        }
        updatePreview();
    });

    // 수동 슬러그 입력 시 자동 생성 비활성화
    slugInput.addEventListener('input', function() {
        this.dataset.autoGenerated = 'false';
        updatePreview();
    });

    // 색상 및 설명 변경 시 미리보기 업데이트
    colorInput.addEventListener('change', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);

    function generateSlug(text) {
        return text
            .toLowerCase()
            .replace(/[^a-z0-9가-힣\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }

    function updatePreview() {
        const name = nameInput.value.trim();
        const slug = slugInput.value.trim();
        const color = colorInput.value;
        const description = descriptionInput.value.trim();

        if (!name) {
            preview.classList.add('hidden');
            return;
        }

        preview.classList.remove('hidden');
        
        // 색상
        document.getElementById('preview-color').style.backgroundColor = color;
        
        // 이름
        document.getElementById('preview-name').textContent = name;
        
        // 슬러그
        document.getElementById('preview-slug').textContent = slug ? `URL: /tags/${slug}` : '';
        
        // 설명
        document.getElementById('preview-description').textContent = description || '설명이 없습니다.';
    }

    // 폼 제출 시 검증
    document.querySelector('form').addEventListener('submit', function(e) {
        const name = nameInput.value.trim();
        const slug = slugInput.value.trim();

        if (!name || !slug) {
            e.preventDefault();
            alert('태그 이름과 슬러그는 필수 항목입니다.');
            return false;
        }

        // 슬러그 형식 검증
        const slugPattern = /^[a-z0-9-]+$/;
        if (!slugPattern.test(slug)) {
            e.preventDefault();
            alert('슬러그는 영문 소문자, 숫자, 하이픈만 사용할 수 있습니다.');
            slugInput.focus();
            return false;
        }
    });
});
</script>
@endsection