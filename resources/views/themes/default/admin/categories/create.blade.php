@extends('themes.default.layouts.app')

@section('title', '새 카테고리 생성')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- 헤더 -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">새 카테고리 생성</h1>
                <p class="text-gray-600 mt-2">블로그의 새로운 카테고리를 생성하세요</p>
            </div>
            <a href="{{ route('admin.categories.index') }}" 
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                목록으로 돌아가기
            </a>
        </div>

        <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">기본 정보</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 카테고리 이름 -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            카테고리 이름 <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}"
                               required
                               maxlength="255"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                               placeholder="예: 기술, 일상, 리뷰 등">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 슬러그 -->
                    <div class="md:col-span-2">
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
                               placeholder="예: technology, daily, review 등 (영문/숫자/하이픈)">
                        <p class="mt-1 text-sm text-gray-500">
                            URL에 사용될 고유한 식별자입니다. 영문, 숫자, 하이픈만 사용 가능합니다.
                        </p>
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 카테고리 타입 -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            카테고리 타입 <span class="text-red-500">*</span>
                        </label>
                        <select id="type" 
                                name="type" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('type') border-red-500 @enderror">
                            <option value="">타입을 선택하세요</option>
                            <option value="post" {{ old('type') === 'post' ? 'selected' : '' }}>포스트 전용</option>
                            <option value="page" {{ old('type') === 'page' ? 'selected' : '' }}>페이지 전용</option>
                            <option value="both" {{ old('type', 'both') === 'both' ? 'selected' : '' }}>포스트 & 페이지 공통</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">
                            이 카테고리가 사용될 콘텐츠 타입을 선택하세요.
                        </p>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 상위 카테고리 -->
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-2">
                            상위 카테고리 (선택사항)
                        </label>
                        <select id="parent_id" 
                                name="parent_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('parent_id') border-red-500 @enderror">
                            <option value="">상위 카테고리 없음 (최상위)</option>
                            @foreach($parentCategories as $category)
                                <option value="{{ $category->id }}" {{ old('parent_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} ({{ $category->type }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-sm text-gray-500">
                            계층형 구조를 만들려면 상위 카테고리를 선택하세요.
                        </p>
                        @error('parent_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 설명 -->
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        설명 (선택사항)
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3" 
                              maxlength="1000"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror"
                              placeholder="이 카테고리에 대한 간단한 설명을 작성하세요...">{{ old('description') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">
                        카테고리 페이지나 관리 화면에서 표시될 설명입니다. (최대 1000자)
                    </p>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 추가 설정 -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">추가 설정</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 정렬 순서 -->
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                            정렬 순서
                        </label>
                        <input type="number" 
                               id="sort_order" 
                               name="sort_order" 
                               value="{{ old('sort_order', 0) }}"
                               min="0"
                               max="999"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('sort_order') border-red-500 @enderror"
                               placeholder="0">
                        <p class="mt-1 text-sm text-gray-500">
                            숫자가 작을수록 먼저 표시됩니다. (기본값: 0)
                        </p>
                        @error('sort_order')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 활성 상태 -->
                    <div>
                        <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">
                            활성 상태
                        </label>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="radio" 
                                       id="is_active_true" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', '1') === '1' ? 'checked' : '' }}
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                <span class="ml-2 text-sm text-gray-700">활성</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" 
                                       id="is_active_false" 
                                       name="is_active" 
                                       value="0" 
                                       {{ old('is_active') === '0' ? 'checked' : '' }}
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                <span class="ml-2 text-sm text-gray-700">비활성</span>
                            </label>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            비활성 상태의 카테고리는 사용자에게 표시되지 않습니다.
                        </p>
                        @error('is_active')
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
                        <span id="preview-name" class="text-lg font-medium text-gray-900"></span>
                        <span id="preview-type" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"></span>
                    </div>
                    <div id="preview-slug" class="text-sm text-gray-500 mb-2"></div>
                    <div id="preview-parent" class="text-sm text-blue-600 mb-2 hidden"></div>
                    <div id="preview-description" class="text-sm text-gray-600"></div>
                </div>
            </div>

            <!-- 액션 버튼 -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.categories.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-200">
                    취소
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    카테고리 생성
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// 실시간 미리보기 및 검증
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const typeSelect = document.getElementById('type');
    const parentSelect = document.getElementById('parent_id');
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

    // 기타 필드 변경 시 미리보기 업데이트
    [typeSelect, parentSelect, descriptionInput].forEach(element => {
        element.addEventListener('change', updatePreview);
    });

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
        const type = typeSelect.value;
        const parentId = parentSelect.value;
        const description = descriptionInput.value.trim();

        if (!name) {
            preview.classList.add('hidden');
            return;
        }

        preview.classList.remove('hidden');
        
        // 이름
        document.getElementById('preview-name').textContent = name;
        
        // 타입
        const typeElement = document.getElementById('preview-type');
        if (type) {
            const typeColors = {
                'post': 'bg-blue-100 text-blue-800',
                'page': 'bg-orange-100 text-orange-800',
                'both': 'bg-purple-100 text-purple-800'
            };
            typeElement.className = `ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${typeColors[type]}`;
            typeElement.textContent = type.charAt(0).toUpperCase() + type.slice(1);
            typeElement.classList.remove('hidden');
        } else {
            typeElement.classList.add('hidden');
        }
        
        // 슬러그
        document.getElementById('preview-slug').textContent = slug ? `URL: /categories/${slug}` : '';
        
        // 상위 카테고리
        const parentElement = document.getElementById('preview-parent');
        if (parentId) {
            const parentOption = parentSelect.querySelector(`option[value="${parentId}"]`);
            if (parentOption) {
                parentElement.textContent = `상위 카테고리: ${parentOption.textContent}`;
                parentElement.classList.remove('hidden');
            }
        } else {
            parentElement.classList.add('hidden');
        }
        
        // 설명
        document.getElementById('preview-description').textContent = description || '설명이 없습니다.';
    }

    // 폼 제출 시 검증
    document.querySelector('form').addEventListener('submit', function(e) {
        const name = nameInput.value.trim();
        const slug = slugInput.value.trim();
        const type = typeSelect.value;

        if (!name || !slug || !type) {
            e.preventDefault();
            alert('필수 항목을 모두 입력해주세요.');
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