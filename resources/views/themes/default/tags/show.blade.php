@extends('themes.default.layouts.app')

@section('title', '#' . $tag->name . ' 태그')
@section('meta_description', $tag->description ?: '#' . $tag->name . ' 태그가 포함된 포스트들을 확인하세요.')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- 태그 헤더 -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center mb-4">
            <div class="w-6 h-6 rounded-full mr-3" style="background-color: {{ $tag->color }}"></div>
            <h1 class="text-4xl font-bold text-gray-900">#{{ $tag->name }}</h1>
        </div>
        
        @if($tag->description)
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ $tag->description }}</p>
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
                        <a href="{{ route('tags.index') }}" class="ml-1 text-gray-700 hover:text-indigo-600 md:ml-2">태그</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-gray-500 md:ml-2">#{{ $tag->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- 태그 통계 -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center">
            <div class="text-2xl font-bold" style="color: {{ $tag->color }}">{{ $posts->total() }}</div>
            <div class="text-sm text-gray-500">전체 포스트</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center">
            <div class="text-2xl font-bold text-gray-600">{{ $tag->created_at->format('Y') }}</div>
            <div class="text-sm text-gray-500">생성 연도</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center">
            <div class="text-2xl font-bold text-gray-600">{{ $averageViews ?? 0 }}</div>
            <div class="text-sm text-gray-500">평균 조회수</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center">
            <div class="text-2xl font-bold text-gray-600">{{ $monthlyPosts ?? 0 }}</div>
            <div class="text-sm text-gray-500">이달의 포스트</div>
        </div>
    </div>

    <!-- 필터 및 정렬 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('tags.show', $tag) }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">검색</label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="이 태그에서 검색..."
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
            
            <div class="min-w-32">
                <label for="period" class="block text-sm font-medium text-gray-700 mb-1">기간</label>
                <select id="period" 
                        name="period" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="" {{ request('period') === '' ? 'selected' : '' }}>전체</option>
                    <option value="week" {{ request('period') === 'week' ? 'selected' : '' }}>최근 1주</option>
                    <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>최근 1개월</option>
                    <option value="year" {{ request('period') === 'year' ? 'selected' : '' }}>최근 1년</option>
                </select>
            </div>
            
            <div class="flex gap-2">
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-200">
                    적용
                </button>
                <a href="{{ route('tags.show', $tag) }}" 
                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-200">
                    초기화
                </a>
            </div>
        </form>
    </div>

    <!-- 포스트 목록 -->
    @if($posts->count() > 0)
        <div class="space-y-6 mb-8">
            @foreach($posts as $post)
                <article class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition duration-200">
                    <div class="md:flex">
                        @if($post->main_image)
                            <div class="md:w-1/3">
                                <img src="{{ asset($post->main_image) }}" 
                                     alt="{{ $post->title }}" 
                                     class="w-full h-48 md:h-full object-cover">
                            </div>
                        @endif
                        
                        <div class="p-6 {{ $post->main_image ? 'md:w-2/3' : 'w-full' }}">
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <time datetime="{{ $post->published_at->format('Y-m-d') }}">
                                    {{ $post->published_at->format('Y년 m월 d일') }}
                                </time>
                                <span class="mx-2">•</span>
                                <span>{{ $post->user->name }}</span>
                                @if($post->category)
                                    <span class="mx-2">•</span>
                                    <a href="{{ route('categories.show', $post->category) }}" 
                                       class="text-indigo-600 hover:text-indigo-800">
                                        {{ $post->category->name }}
                                    </a>
                                @endif
                                @if($post->views_count > 0)
                                    <span class="mx-2">•</span>
                                    <span>조회 {{ number_format($post->views_count) }}</span>
                                @endif
                            </div>
                            
                            <h2 class="text-xl font-semibold text-gray-900 mb-3">
                                <a href="{{ route('posts.show', $post) }}" class="hover:text-indigo-600">
                                    {{ $post->title }}
                                </a>
                            </h2>
                            
                            @if($post->summary)
                                <p class="text-gray-600 line-clamp-3 mb-4">{{ $post->summary }}</p>
                            @endif
                            
                            <!-- 모든 태그 표시 (현재 태그 강조) -->
                            @if($post->tags->count() > 0)
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @foreach($post->tags as $postTag)
                                        <a href="{{ route('tags.show', $postTag) }}" 
                                           class="inline-block px-2 py-1 text-xs rounded-full transition duration-200
                                                  {{ $postTag->id === $tag->id ? 'font-semibold' : '' }}"
                                           style="background-color: {{ $postTag->color }}20; color: {{ $postTag->color }};">
                                            #{{ $postTag->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                            
                            <a href="{{ route('posts.show', $post) }}" 
                               class="text-indigo-600 font-medium hover:text-indigo-800">
                                계속 읽기 →
                            </a>
                        </div>
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
            <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center" 
                 style="background-color: {{ $tag->color }}20;">
                <svg class="w-8 h-8" style="color: {{ $tag->color }}" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <h3 class="mt-2 text-sm font-medium text-gray-900">포스트가 없습니다</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if(request()->hasAny(['search', 'sort', 'period']))
                    검색 조건에 맞는 포스트를 찾을 수 없습니다.
                @else
                    이 태그가 사용된 포스트가 아직 없습니다.
                @endif
            </p>
            <div class="mt-6">
                @if(request()->hasAny(['search', 'sort', 'period']))
                    <a href="{{ route('tags.show', $tag) }}" 
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

    <!-- 관련 태그 추천 -->
    @if(isset($relatedTags) && $relatedTags->count() > 0)
        <div class="mt-12 bg-gray-50 rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">관련 태그</h2>
            <div class="flex flex-wrap gap-3">
                @foreach($relatedTags as $related)
                    <a href="{{ route('tags.show', $related) }}" 
                       class="inline-flex items-center px-3 py-2 bg-white rounded-full hover:shadow-sm transition duration-200">
                        <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $related->color }}"></div>
                        <span class="text-sm font-medium text-gray-700">#{{ $related->name }}</span>
                        <span class="ml-2 text-xs text-gray-500">({{ $related->posts_count ?? 0 }})</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 태그 클라우드 -->
    @if(isset($tagCloud) && $tagCloud->count() > 0)
        <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">인기 태그</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($tagCloud as $cloudTag)
                    <a href="{{ route('tags.show', $cloudTag) }}" 
                       class="inline-block px-3 py-1 rounded-full transition duration-200 hover:opacity-80
                              {{ $cloudTag->id === $tag->id ? 'font-bold ring-2 ring-offset-1' : '' }}"
                       style="background-color: {{ $cloudTag->color }}20; color: {{ $cloudTag->color }};
                              {{ $cloudTag->id === $tag->id ? 'ring-color: ' . $cloudTag->color : '' }}">
                        #{{ $cloudTag->name }}
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

// 정렬/기간 변경시 자동 제출
document.getElementById('sort').addEventListener('change', function() {
    document.querySelector('form').submit();
});

document.getElementById('period').addEventListener('change', function() {
    document.querySelector('form').submit();
});
</script>
@endpush
@endsection