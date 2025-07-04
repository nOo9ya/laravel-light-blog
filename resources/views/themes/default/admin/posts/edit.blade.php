@extends('themes.default.layouts.app')

@section('title', '포스트 수정: ' . $post->title)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">포스트 수정</h1>
            <div class="flex space-x-2">
                <a href="{{ route('admin.posts.show', $post) }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition duration-200">
                    미리보기
                </a>
                <a href="{{ route('admin.posts.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                    목록으로 돌아가기
                </a>
            </div>
        </div>

        <!-- 에러 메시지 -->
        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <h3 class="font-bold">다음 오류를 확인해주세요:</h3>
            <ul class="mt-2 list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <!-- 제목 -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">제목 <span class="text-red-500">*</span></label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title', $post->title) }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                    @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 내용 -->
                <div class="mb-6">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">내용 <span class="text-red-500">*</span></label>
                    <div id="post-content-editor"></div>
                    <input type="hidden" id="content" name="content" value="{{ old('content', $post->content) }}">
                    @error('content')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 요약 -->
                <div class="mb-6">
                    <label for="summary" class="block text-sm font-medium text-gray-700 mb-2">요약</label>
                    <textarea id="summary" 
                              name="summary" 
                              rows="3" 
                              placeholder="포스트 요약을 입력하세요. 비워두면 내용의 일부가 자동으로 사용됩니다."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('summary') border-red-500 @enderror">{{ old('summary', $post->summary) }}</textarea>
                    @error('summary')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 메타데이터 -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">포스트 설정</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 카테고리 -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">카테고리</label>
                        <select id="category_id" name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">카테고리 선택</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 상태 -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">상태 <span class="text-red-500">*</span></label>
                        <select id="status" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="draft" {{ old('status', $post->status) == 'draft' ? 'selected' : '' }}>초안</option>
                            <option value="published" {{ old('status', $post->status) == 'published' ? 'selected' : '' }}>발행</option>
                            <option value="archived" {{ old('status', $post->status) == 'archived' ? 'selected' : '' }}>보관</option>
                        </select>
                        @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 발행일 -->
                    <div>
                        <label for="published_at" class="block text-sm font-medium text-gray-700 mb-2">발행일</label>
                        <input type="datetime-local" 
                               id="published_at" 
                               name="published_at" 
                               value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('published_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 태그 -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">태그</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        @foreach($tags as $tag)
                        <label class="inline-flex items-center">
                            <input type="checkbox" 
                                   name="tags[]" 
                                   value="{{ $tag->id }}"
                                   {{ in_array($tag->id, old('tags', $post->tags->pluck('id')->toArray())) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">{{ $tag->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('tags')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 이미지 업로드 -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">이미지 설정</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 대표 이미지 -->
                    <div>
                        <label for="main_image" class="block text-sm font-medium text-gray-700 mb-2">대표 이미지</label>
                        @if($post->main_image)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $post->main_image) }}" alt="현재 대표 이미지" class="w-32 h-32 object-cover rounded-lg">
                            <label class="inline-flex items-center mt-2">
                                <input type="checkbox" name="remove_main_image" value="1" class="rounded border-gray-300 text-red-600">
                                <span class="ml-2 text-sm text-red-600">현재 이미지 삭제</span>
                            </label>
                        </div>
                        @endif
                        <input type="file" 
                               id="main_image" 
                               name="main_image" 
                               accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">JPG, PNG, GIF, WebP 형식 지원 (최대 10MB)</p>
                        @error('main_image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- OG 이미지 -->
                    <div>
                        <label for="og_image" class="block text-sm font-medium text-gray-700 mb-2">OG 이미지</label>
                        @if($post->og_image)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $post->og_image) }}" alt="현재 OG 이미지" class="w-32 h-20 object-cover rounded-lg">
                            <label class="inline-flex items-center mt-2">
                                <input type="checkbox" name="remove_og_image" value="1" class="rounded border-gray-300 text-red-600">
                                <span class="ml-2 text-sm text-red-600">현재 이미지 삭제</span>
                            </label>
                        </div>
                        @endif
                        <input type="file" 
                               id="og_image" 
                               name="og_image" 
                               accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">소셜 공유용 이미지 (최소 1200x630px)</p>
                        @error('og_image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- SEO 설정 -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">SEO 설정</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="seo_og_title" class="block text-sm font-medium text-gray-700 mb-2">OG 제목</label>
                        <input type="text" 
                               id="seo_og_title" 
                               name="seo[og_title]" 
                               value="{{ old('seo.og_title', $post->seoMeta?->og_title) }}"
                               placeholder="비워두면 포스트 제목이 사용됩니다"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('seo.og_title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="seo_og_description" class="block text-sm font-medium text-gray-700 mb-2">OG 설명</label>
                        <textarea id="seo_og_description" 
                                  name="seo[og_description]" 
                                  rows="3" 
                                  placeholder="비워두면 포스트 요약이 사용됩니다"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('seo.og_description', $post->seoMeta?->og_description) }}</textarea>
                        @error('seo.og_description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="seo_meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">메타 키워드</label>
                        <input type="text" 
                               id="seo_meta_keywords" 
                               name="seo[meta_keywords]" 
                               value="{{ old('seo.meta_keywords', is_array($post->seoMeta?->meta_keywords) ? implode(', ', $post->seoMeta->meta_keywords) : $post->seoMeta?->meta_keywords) }}"
                               placeholder="키워드1, 키워드2, 키워드3"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('seo.meta_keywords')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="seo_robots" class="block text-sm font-medium text-gray-700 mb-2">로봇 설정</label>
                        <select id="seo_robots" name="seo[robots]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">기본값</option>
                            <option value="index, follow" {{ old('seo.robots', $post->seoMeta?->robots) == 'index, follow' ? 'selected' : '' }}>인덱스 허용</option>
                            <option value="noindex, nofollow" {{ old('seo.robots', $post->seoMeta?->robots) == 'noindex, nofollow' ? 'selected' : '' }}>인덱스 차단</option>
                        </select>
                        @error('seo.robots')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- 제출 버튼 -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.posts.show', $post) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition duration-200">
                    취소
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                    포스트 수정
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// 에디터 초기 콘텐츠 설정
document.addEventListener('DOMContentLoaded', function() {
    const contentElement = document.getElementById('content');
    if (contentElement && window.initializeEditor) {
        const editor = window.initializeEditor('post-content-editor', {
            initialValue: contentElement.value
        });
    }
});
</script>
@endsection