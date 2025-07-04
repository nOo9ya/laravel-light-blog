@extends('themes.default.layouts.app')

@section('title', $post->title)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- 헤더 -->
        <div class="flex justify-between items-start mb-6">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $post->title }}</h1>
                <div class="flex items-center space-x-4 text-sm text-gray-600">
                    <span>작성자: {{ $post->user->name }}</span>
                    <span>작성일: {{ $post->created_at->format('Y-m-d H:i') }}</span>
                    @if($post->published_at)
                    <span>발행일: {{ $post->published_at->format('Y-m-d H:i') }}</span>
                    @endif
                    <span>조회수: {{ number_format($post->views_count) }}</span>
                </div>
            </div>
            
            <div class="flex space-x-2 ml-4">
                <a href="{{ route('admin.posts.edit', $post) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                    수정
                </a>
                <a href="{{ route('admin.posts.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                    목록
                </a>
                @if($post->status === 'published')
                <a href="{{ route('posts.show', $post) }}" target="_blank" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition duration-200">
                    공개 페이지
                </a>
                @endif
            </div>
        </div>

        <!-- 상태 및 메타 정보 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- 상태 정보 -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">포스트 상태</h3>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">상태:</span>
                            @if($post->status == 'published')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">발행</span>
                            @elseif($post->status == 'draft')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">초안</span>
                            @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">보관</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">슬러그:</span>
                            <code class="text-sm bg-gray-100 px-2 py-1 rounded">{{ $post->slug }}</code>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">읽기 시간:</span>
                            <span class="text-gray-800">{{ $post->reading_time }}분</span>
                        </div>
                    </div>
                </div>

                <!-- 분류 정보 -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">분류</h3>
                    <div class="space-y-2">
                        <div>
                            <span class="text-gray-600">카테고리:</span>
                            @if($post->category)
                            <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $post->category->name }}
                            </span>
                            @else
                            <span class="ml-2 text-gray-400">미분류</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-600">태그:</span>
                            <div class="mt-1 flex flex-wrap gap-1">
                                @forelse($post->tags as $tag)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ $tag->name }}
                                </span>
                                @empty
                                <span class="text-gray-400">태그 없음</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 이미지 정보 -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">이미지</h3>
                    <div class="space-y-3">
                        @if($post->main_image)
                        <div>
                            <span class="text-gray-600 text-sm">대표 이미지:</span>
                            <img src="{{ asset('storage/' . $post->main_image) }}" alt="대표 이미지" class="w-full h-24 object-cover rounded-lg mt-1">
                        </div>
                        @endif
                        @if($post->og_image)
                        <div>
                            <span class="text-gray-600 text-sm">OG 이미지:</span>
                            <img src="{{ asset('storage/' . $post->og_image) }}" alt="OG 이미지" class="w-full h-16 object-cover rounded-lg mt-1">
                        </div>
                        @endif
                        @if(!$post->main_image && !$post->og_image)
                        <span class="text-gray-400">이미지 없음</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 요약 -->
        @if($post->summary)
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <h3 class="text-lg font-semibold text-blue-800 mb-2">요약</h3>
            <p class="text-blue-700">{{ $post->summary }}</p>
        </div>
        @endif

        <!-- 본문 내용 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">본문 내용</h3>
            <div class="prose max-w-none">
                {!! $post->content !!}
            </div>
        </div>

        <!-- SEO 메타 정보 -->
        @if($post->seoMeta)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">SEO 설정</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">OG 제목</label>
                            <p class="mt-1 text-sm text-gray-600">{{ $post->seoMeta->og_title ?: '포스트 제목 사용' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">OG 설명</label>
                            <p class="mt-1 text-sm text-gray-600">{{ $post->seoMeta->og_description ?: '포스트 요약 사용' }}</p>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">메타 키워드</label>
                            @if($post->seoMeta->meta_keywords)
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach($post->seoMeta->meta_keywords as $keyword)
                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">{{ $keyword }}</span>
                                @endforeach
                            </div>
                            @else
                            <p class="mt-1 text-sm text-gray-400">설정되지 않음</p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">로봇 설정</label>
                            <p class="mt-1 text-sm text-gray-600">{{ $post->seoMeta->robots ?: '기본값' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- 첨부파일 -->
        @if($post->attachments->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">첨부파일</h3>
            <div class="space-y-2">
                @foreach($post->attachments as $attachment)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            @if($attachment->type === 'image')
                            <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            @else
                            <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $attachment->original_name }}</p>
                            <p class="text-sm text-gray-500">{{ $attachment->formatted_size }} • {{ ucfirst($attachment->type) }}</p>
                        </div>
                    </div>
                    <a href="{{ asset('storage/' . $attachment->file_path) }}" 
                       target="_blank" 
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        다운로드
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 삭제 확인 -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-red-800 mb-2">위험 구역</h3>
            <p class="text-red-700 text-sm mb-4">포스트를 삭제하면 모든 관련 데이터가 영구적으로 삭제됩니다.</p>
            <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="inline-block">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200"
                        onclick="return confirm('정말로 이 포스트를 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.')">
                    포스트 삭제
                </button>
            </form>
        </div>
    </div>
</div>
@endsection