@extends('themes.default.layouts.app')

@php
    $seoData = \App\Services\SeoService::getPostSeoData($post);
    $jsonLd = \App\Services\SeoService::getJsonLd($post);
@endphp

@section('title', $seoData['title'])

@section('seo_meta')
{!! \App\Services\SeoService::generateMetaTags($seoData) !!}
@endsection

@section('json_ld')
<script type="application/ld+json">
{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endsection

@section('content')
<article class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- 브레드크럼 -->
        <nav class="mb-8 text-sm text-gray-600">
            <ol class="flex items-center space-x-2">
                <li><a href="{{ route('home') }}" class="hover:text-blue-600">홈</a></li>
                <li><span class="mx-2">/</span></li>
                <li><a href="{{ route('posts.index') }}" class="hover:text-blue-600">블로그</a></li>
                @if($post->category)
                <li><span class="mx-2">/</span></li>
                <li><a href="{{ route('categories.show', $post->category->slug) }}" class="hover:text-blue-600">{{ $post->category->name }}</a></li>
                @endif
                <li><span class="mx-2">/</span></li>
                <li class="text-gray-400">{{ $post->title }}</li>
            </ol>
        </nav>

        <!-- 포스트 헤더 -->
        <header class="mb-8">
            <!-- 카테고리 -->
            @if($post->category)
            <div class="mb-4">
                <a href="{{ route('categories.show', $post->category->slug) }}" 
                   class="inline-block px-3 py-1 text-sm font-semibold text-blue-600 bg-blue-100 rounded-full hover:bg-blue-200 transition duration-200">
                    {{ $post->category->name }}
                </a>
            </div>
            @endif

            <!-- 제목 -->
            <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6 leading-tight">
                {{ $post->title }}
            </h1>

            <!-- 요약 -->
            @if($post->summary)
            <div class="bg-blue-50 border-l-4 border-blue-400 p-6 mb-6">
                <p class="text-lg text-blue-800 leading-relaxed">{{ $post->summary }}</p>
            </div>
            @endif

            <!-- 메타 정보 -->
            <div class="flex flex-wrap items-center gap-6 text-gray-600 text-sm border-b border-gray-200 pb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>{{ $post->user->name }}</span>
                </div>
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <time datetime="{{ $post->published_at->toISOString() }}">
                        {{ $post->published_at->format('Y년 m월 d일') }}
                    </time>
                </div>
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span>{{ number_format($post->views_count) }}회 조회</span>
                </div>
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ $post->reading_time }}분 소요</span>
                </div>
            </div>
        </header>

        <!-- 대표 이미지 -->
        @if($post->main_image)
        <div class="mb-8">
            <img src="{{ asset('storage/' . $post->main_image) }}" 
                 alt="{{ $post->title }}" 
                 class="w-full rounded-lg shadow-lg">
        </div>
        @endif

        <!-- 포스트 내용 -->
        <div class="prose prose-lg max-w-none mb-12">
            {!! $post->content !!}
        </div>

        <!-- 태그 -->
        @if($post->tags->count() > 0)
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">태그</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($post->tags as $tag)
                <a href="{{ route('tags.show', $tag->slug) }}" 
                   class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full hover:bg-gray-200 transition duration-200">
                    #{{ $tag->name }}
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 공유 버튼 -->
        <div class="mb-8 p-6 bg-gray-50 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">이 포스트 공유하기</h3>
            <div class="flex space-x-4">
                <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(request()->fullUrl()) }}" 
                   target="_blank" 
                   class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-200">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                    </svg>
                    Twitter
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" 
                   target="_blank" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    Facebook
                </a>
                <button onclick="copyToClipboard()" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    링크 복사
                </button>
            </div>
        </div>

        <!-- 관련 포스트 또는 네비게이션 -->
        <div class="border-t border-gray-200 pt-8">
            <div class="flex justify-between items-center">
                <a href="{{ route('posts.index') }}" 
                   class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    포스트 목록으로
                </a>
                
                @auth
                @if(auth()->user()->hasRole('admin'))
                <a href="{{ route('admin.posts.edit', $post) }}" 
                   class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition duration-200">
                    포스트 수정
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                </a>
                @endif
                @endauth
            </div>
        </div>
    </div>
</article>

<script>
function copyToClipboard() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        alert('링크가 클립보드에 복사되었습니다!');
    }, function(err) {
        console.error('클립보드 복사 실패: ', err);
    });
}
</script>

<style>
.prose {
    color: #374151;
    line-height: 1.75;
}

.prose p {
    margin-bottom: 1.25em;
}

.prose h2 {
    font-size: 1.875em;
    font-weight: 700;
    margin-top: 2em;
    margin-bottom: 1em;
    color: #111827;
}

.prose h3 {
    font-size: 1.5em;
    font-weight: 600;
    margin-top: 1.6em;
    margin-bottom: 0.6em;
    color: #111827;
}

.prose h4 {
    font-size: 1.25em;
    font-weight: 600;
    margin-top: 1.5em;
    margin-bottom: 0.5em;
    color: #111827;
}

.prose ul {
    margin: 1.25em 0;
    padding-left: 1.625em;
}

.prose ol {
    margin: 1.25em 0;
    padding-left: 1.625em;
}

.prose li {
    margin: 0.5em 0;
}

.prose blockquote {
    font-style: italic;
    border-left: 4px solid #e5e7eb;
    padding-left: 1em;
    margin: 1.6em 0;
    color: #6b7280;
}

.prose code {
    background-color: #f3f4f6;
    padding: 0.125em 0.25em;
    border-radius: 0.25rem;
    font-size: 0.875em;
}

.prose pre {
    background-color: #1f2937;
    color: #f9fafb;
    padding: 1em;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 1.5em 0;
}

.prose pre code {
    background-color: transparent;
    padding: 0;
    color: inherit;
}

.prose img {
    margin: 2em 0;
    border-radius: 0.5rem;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}

.prose a {
    color: #2563eb;
    text-decoration: underline;
}

.prose a:hover {
    color: #1d4ed8;
}
</style>
@endsection