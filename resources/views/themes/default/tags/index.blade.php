@extends('themes.default.layouts.app')

@section('title', '태그 목록')
@section('meta_description', '모든 태그를 둘러보고 관심 있는 주제의 포스트를 찾아보세요.')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- 페이지 헤더 -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">태그</h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            태그를 통해 원하는 주제의 포스트를 빠르게 찾아보세요. 각 태그는 관련된 모든 포스트를 한눈에 보여줍니다.
        </p>
    </div>

    <!-- 검색 및 필터 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-8">
        <form method="GET" action="{{ route('tags.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">검색</label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="태그 이름으로 검색..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div class="min-w-32">
                <label for="color" class="block text-sm font-medium text-gray-700 mb-1">색상</label>
                <select id="color" 
                        name="color" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">전체</option>
                    <option value="#3b82f6" {{ request('color') === '#3b82f6' ? 'selected' : '' }}>🔵 파란색</option>
                    <option value="#ef4444" {{ request('color') === '#ef4444' ? 'selected' : '' }}>🔴 빨간색</option>
                    <option value="#10b981" {{ request('color') === '#10b981' ? 'selected' : '' }}>🟢 초록색</option>
                    <option value="#f59e0b" {{ request('color') === '#f59e0b' ? 'selected' : '' }}>🟡 노란색</option>
                    <option value="#8b5cf6" {{ request('color') === '#8b5cf6' ? 'selected' : '' }}>🟣 보라색</option>
                    <option value="#6b7280" {{ request('color') === '#6b7280' ? 'selected' : '' }}>⚫ 회색</option>
                </select>
            </div>
            
            <div class="min-w-32">
                <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">정렬</label>
                <select id="sort" 
                        name="sort" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="name" {{ request('sort', 'name') === 'name' ? 'selected' : '' }}>이름순</option>
                    <option value="posts_count" {{ request('sort') === 'posts_count' ? 'selected' : '' }}>포스트 수</option>
                    <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>최신순</option>
                </select>
            </div>
            
            <div class="flex gap-2">
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-200">
                    검색
                </button>
                <a href="{{ route('tags.index') }}" 
                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-200">
                    초기화
                </a>
            </div>
        </form>
    </div>

    <!-- 통계 정보 -->
    @if($stats)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-indigo-600">{{ $stats['total'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">전체 태그</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $stats['used'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">사용된 태그</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $stats['popular'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">인기 태그</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $stats['total_posts'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">전체 포스트</div>
            </div>
        </div>
    @endif

    <!-- 태그 클라우드 (크기별 표시) -->
    @if($tagCloud && $tagCloud->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">인기 태그 클라우드</h2>
            <div class="flex flex-wrap gap-3 justify-center">
                @foreach($tagCloud as $cloudTag)
                    @php
                        $postCount = $cloudTag->posts_count ?? 0;
                        $fontSize = match(true) {
                            $postCount >= 20 => 'text-2xl',
                            $postCount >= 10 => 'text-xl',
                            $postCount >= 5 => 'text-lg',
                            default => 'text-base'
                        };
                        $fontWeight = $postCount >= 10 ? 'font-bold' : 'font-medium';
                    @endphp
                    <a href="{{ route('tags.show', $cloudTag) }}" 
                       class="inline-block px-3 py-2 rounded-full transition duration-200 hover:scale-105 {{ $fontSize }} {{ $fontWeight }}"
                       style="background-color: {{ $cloudTag->color }}20; color: {{ $cloudTag->color }};"
                       title="{{ $cloudTag->name }} ({{ $postCount }}개 포스트)">
                        #{{ $cloudTag->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 태그 목록 -->
    @if($tags->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($tags as $tag)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition duration-200">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center flex-1">
                            <div class="w-4 h-4 rounded-full mr-3 flex-shrink-0" style="background-color: {{ $tag->color }}"></div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                <a href="{{ route('tags.show', $tag) }}" class="hover:text-indigo-600">
                                    #{{ $tag->name }}
                                </a>
                            </h3>
                        </div>
                        
                        <!-- 포스트 수 배지 -->
                        @if($tag->posts_count > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $tag->posts_count }}개
                            </span>
                        @endif
                    </div>
                    
                    @if($tag->description)
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $tag->description }}</p>
                    @endif
                    
                    <!-- 메타 정보 -->
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>{{ $tag->created_at->format('Y.m.d') }} 생성</span>
                        @if($tag->posts_count > 0)
                            <a href="{{ route('tags.show', $tag) }}" 
                               class="text-indigo-600 hover:text-indigo-800 font-medium">
                                포스트 보기 →
                            </a>
                        @else
                            <span class="text-gray-400">포스트 없음</span>
                        @endif
                    </div>
                    
                    <!-- 최근 포스트 미리보기 -->
                    @if(isset($tag->recent_posts) && $tag->recent_posts->count() > 0)
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="text-xs font-medium text-gray-700 mb-2">최근 포스트:</div>
                            <div class="space-y-1">
                                @foreach($tag->recent_posts->take(2) as $recentPost)
                                    <div class="text-xs text-gray-600 truncate">
                                        <a href="{{ route('posts.show', $recentPost) }}" 
                                           class="hover:text-indigo-600">
                                            • {{ $recentPost->title }}
                                        </a>
                                    </div>
                                @endforeach
                                @if($tag->recent_posts->count() > 2)
                                    <div class="text-xs text-gray-400">
                                        +{{ $tag->recent_posts->count() - 2 }}개 더
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- 페이지네이션 -->
        @if($tags->hasPages())
            <div class="flex justify-center">
                {{ $tags->appends(request()->query())->links() }}
            </div>
        @endif
    @else
        <!-- 빈 상태 -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">태그가 없습니다</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if(request()->hasAny(['search', 'color', 'sort']))
                    검색 조건에 맞는 태그를 찾을 수 없습니다.
                @else
                    아직 생성된 태그가 없습니다.
                @endif
            </p>
            <div class="mt-6">
                @if(request()->hasAny(['search', 'color', 'sort']))
                    <a href="{{ route('tags.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        전체 태그 보기
                    </a>
                @else
                    <a href="{{ route('posts.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        포스트 보기
                    </a>
                @endif
            </div>
        </div>
    @endif

    <!-- 색상별 태그 분류 -->
    @if(isset($tagsByColor) && count($tagsByColor) > 0)
        <div class="mt-12 bg-gray-50 rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">색상별 태그</h2>
            <div class="space-y-4">
                @foreach($tagsByColor as $color => $colorTags)
                    <div class="flex items-start">
                        <div class="w-6 h-6 rounded-full mr-4 mt-1 flex-shrink-0" style="background-color: {{ $color }}"></div>
                        <div class="flex-1">
                            <div class="flex flex-wrap gap-2">
                                @foreach($colorTags as $colorTag)
                                    <a href="{{ route('tags.show', $colorTag) }}" 
                                       class="inline-block px-3 py-1 rounded-full text-sm transition duration-200 hover:opacity-80"
                                       style="background-color: {{ $color }}20; color: {{ $color }};">
                                        #{{ $colorTag->name }}
                                        @if($colorTag->posts_count > 0)
                                            <span class="text-xs opacity-75">({{ $colorTag->posts_count }})</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
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

// 정렬/색상 변경시 자동 제출
document.getElementById('sort').addEventListener('change', function() {
    document.querySelector('form').submit();
});

document.getElementById('color').addEventListener('change', function() {
    document.querySelector('form').submit();
});
</script>
@endpush
@endsection