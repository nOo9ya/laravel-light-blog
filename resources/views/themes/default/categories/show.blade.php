@extends('themes.default.layouts.app')

@section('title', $category->name . ' 카테고리')
@section('meta_description', $category->description ?: $category->name . ' 카테고리의 포스트들을 확인하세요.')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- 카테고리 헤더 -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $category->name }}</h1>
        @if($category->description)
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ $category->description }}</p>
        @endif
        
        <!-- 브레드크럼 -->
        <nav class="flex justify-center mt-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-indigo-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L9 5.414V17a1 1 0 102 0V5.414l5.293 5.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        홈
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('categories.index') }}" class="ml-1 text-gray-700 hover:text-indigo-600 md:ml-2">카테고리</a>
                    </div>
                </li>
                @if($category->parent)
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <a href="{{ route('categories.show', $category->parent) }}" class="ml-1 text-gray-700 hover:text-indigo-600 md:ml-2">{{ $category->parent->name }}</a>
                        </div>
                    </li>
                @endif
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-gray-500 md:ml-2">{{ $category->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- 하위 카테고리 -->
    @if($category->children && $category->children->count() > 0)
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">하위 카테고리</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($category->children as $child)
                    <a href="{{ route('categories.show', $child) }}" 
                       class="p-4 bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition duration-200">
                        <h3 class="font-medium text-gray-900">{{ $child->name }}</h3>
                        @if($child->description)
                            <p class="text-sm text-gray-500 mt-1">{{ Str::limit($child->description, 50) }}</p>
                        @endif
                        <div class="text-xs text-gray-400 mt-2">
                            {{ $child->posts_count ?? 0 }}개 포스트
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 필터 및 정렬 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('categories.show', $category) }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">검색</label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="이 카테고리에서 검색..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div class="min-w-32">
                <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">정렬</label>
                <select id="sort" 
                        name="sort" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>최신순</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>오래된순</option>
                    <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>인기순</option>
                    <option value="title" {{ request('sort') === 'title' ? 'selected' : '' }}>제목순</option>
                </select>
            </div>
            
            <div class="flex gap-2">
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-200">
                    적용
                </button>
                <a href="{{ route('categories.show', $category) }}" 
                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-200">
                    초기화
                </a>
            </div>
        </form>
    </div>

    <!-- 포스트 목록 -->
    @if($posts->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($posts as $post)
                <article class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition duration-200">
                    @if($post->main_image)
                        <div class="aspect-w-16 aspect-h-9">
                            <img src="{{ asset($post->main_image) }}" 
                                 alt="{{ $post->title }}" 
                                 class="w-full h-48 object-cover">
                        </div>
                    @endif
                    
                    <div class="p-6">
                        <div class="flex items-center text-sm text-gray-500 mb-2">
                            <time datetime="{{ $post->published_at->format('Y-m-d') }}">
                                {{ $post->published_at->format('Y년 m월 d일') }}
                            </time>
                            <span class="mx-2">•</span>
                            <span>{{ $post->user->name }}</span>
                            @if($post->views_count > 0)
                                <span class="mx-2">•</span>
                                <span>조회 {{ number_format($post->views_count) }}</span>
                            @endif
                        </div>
                        
                        <h2 class="text-xl font-semibold text-gray-900 mb-3 line-clamp-2">
                            <a href="{{ route('posts.show', $post) }}" class="hover:text-indigo-600">
                                {{ $post->title }}
                            </a>
                        </h2>
                        
                        @if($post->summary)
                            <p class="text-gray-600 line-clamp-3 mb-4">{{ $post->summary }}</p>
                        @endif
                        
                        <!-- 태그 -->
                        @if($post->tags->count() > 0)
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach($post->tags->take(3) as $tag)
                                    <a href="{{ route('tags.show', $tag) }}" 
                                       class="inline-block px-2 py-1 text-xs rounded-full"
                                       style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                                        #{{ $tag->name }}
                                    </a>
                                @endforeach
                                @if($post->tags->count() > 3)
                                    <span class="text-xs text-gray-400">+{{ $post->tags->count() - 3 }}개</span>
                                @endif
                            </div>
                        @endif
                        
                        <a href="{{ route('posts.show', $post) }}" 
                           class="text-indigo-600 font-medium hover:text-indigo-800">
                            계속 읽기 →
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <!-- 페이지네이션 -->
        @if($posts->hasPages())
            <div class="flex justify-center">
                {{ $posts->appends(request()->query())->links() }}
            </div>
        @endif
    @else
        <!-- 빈 상태 -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">포스트가 없습니다</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if(request()->hasAny(['search', 'sort']))
                    검색 조건에 맞는 포스트를 찾을 수 없습니다.
                @else
                    이 카테고리에는 아직 포스트가 없습니다.
                @endif
            </p>
            <div class="mt-6">
                @if(request()->hasAny(['search', 'sort']))
                    <a href="{{ route('categories.show', $category) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        전체 포스트 보기
                    </a>
                @else
                    <a href="{{ route('posts.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        다른 포스트 보기
                    </a>
                @endif
            </div>
        </div>
    @endif

    <!-- 관련 카테고리 추천 -->
    @if($relatedCategories && $relatedCategories->count() > 0)
        <div class="mt-12 bg-gray-50 rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">관련 카테고리</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($relatedCategories as $related)
                    <a href="{{ route('categories.show', $related) }}" 
                       class="p-3 bg-white rounded-md hover:shadow-sm transition duration-200">
                        <h3 class="font-medium text-gray-900 text-sm">{{ $related->name }}</h3>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $related->posts_count ?? 0 }}개 포스트
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
// 검색 폼 자동 제출 (디바운스)
let searchTimeout;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        document.querySelector('form').submit();
    }, 500);
});

// 정렬 변경시 자동 제출
document.getElementById('sort').addEventListener('change', function() {
    document.querySelector('form').submit();
});
</script>
@endpush
@endsection