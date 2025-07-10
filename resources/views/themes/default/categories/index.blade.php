@extends('themes.default.layouts.app')

@section('title', '카테고리 목록')
@section('meta_description', '모든 카테고리를 둘러보고 관심 있는 주제의 포스트를 찾아보세요.')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- 페이지 헤더 -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">카테고리</h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            관심 있는 주제별로 포스트를 탐색해보세요. 각 카테고리에는 관련된 다양한 글들이 정리되어 있습니다.
        </p>
    </div>

    <!-- 검색 및 필터 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-8">
        <form method="GET" action="{{ route('categories.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">검색</label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="카테고리 이름으로 검색..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div class="min-w-32">
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">타입</label>
                <select id="type" 
                        name="type" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">전체</option>
                    <option value="post" {{ request('type') === 'post' ? 'selected' : '' }}>포스트</option>
                    <option value="page" {{ request('type') === 'page' ? 'selected' : '' }}>페이지</option>
                    <option value="both" {{ request('type') === 'both' ? 'selected' : '' }}>공통</option>
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
                <a href="{{ route('categories.index') }}" 
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
                <div class="text-sm text-gray-500">전체 카테고리</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $stats['post_categories'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">포스트 카테고리</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $stats['page_categories'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">페이지 카테고리</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $stats['total_posts'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">전체 포스트</div>
            </div>
        </div>
    @endif

    <!-- 카테고리 목록 -->
    @if($categories->count() > 0)
        <div class="space-y-6">
            @foreach($categories as $category)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition duration-200">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <h2 class="text-xl font-semibold text-gray-900">
                                    <a href="{{ route('categories.show', $category) }}" class="hover:text-indigo-600">
                                        {{ $category->name }}
                                    </a>
                                </h2>
                                
                                <!-- 타입 배지 -->
                                <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($category->type === 'post') bg-blue-100 text-blue-800
                                    @elseif($category->type === 'page') bg-orange-100 text-orange-800
                                    @else bg-purple-100 text-purple-800 @endif">
                                    {{ ucfirst($category->type) }}
                                </span>
                            </div>
                            
                            @if($category->description)
                                <p class="text-gray-600 mb-3">{{ $category->description }}</p>
                            @endif
                            
                            <!-- 메타 정보 -->
                            <div class="flex items-center text-sm text-gray-500 space-x-4">
                                <span>{{ $category->posts_count ?? 0 }}개 포스트</span>
                                @if($category->parent)
                                    <span>상위: 
                                        <a href="{{ route('categories.show', $category->parent) }}" 
                                           class="text-indigo-600 hover:text-indigo-800">
                                            {{ $category->parent->name }}
                                        </a>
                                    </span>
                                @endif
                                <span>{{ $category->created_at->format('Y.m.d') }} 생성</span>
                            </div>
                            
                            <!-- 하위 카테고리 -->
                            @if($category->children && $category->children->count() > 0)
                                <div class="mt-4">
                                    <div class="text-sm font-medium text-gray-700 mb-2">하위 카테고리:</div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($category->children as $child)
                                            <a href="{{ route('categories.show', $child) }}" 
                                               class="inline-block px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition duration-200">
                                                {{ $child->name }} ({{ $child->posts_count ?? 0 }})
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- 액션 버튼 -->
                        <div class="ml-6 flex flex-col space-y-2">
                            <a href="{{ route('categories.show', $category) }}" 
                               class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition duration-200 text-center">
                                포스트 보기
                            </a>
                            
                            @if($category->posts_count > 0)
                                <div class="text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $category->posts_count }}개
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- 페이지네이션 -->
        @if($categories->hasPages())
            <div class="flex justify-center mt-8">
                {{ $categories->appends(request()->query())->links() }}
            </div>
        @endif
    @else
        <!-- 빈 상태 -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-4H5m14 8H5m14 4H5"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">카테고리가 없습니다</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if(request()->hasAny(['search', 'type', 'sort']))
                    검색 조건에 맞는 카테고리를 찾을 수 없습니다.
                @else
                    아직 생성된 카테고리가 없습니다.
                @endif
            </p>
            <div class="mt-6">
                @if(request()->hasAny(['search', 'type', 'sort']))
                    <a href="{{ route('categories.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        전체 카테고리 보기
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

    <!-- 인기 카테고리 -->
    @if(isset($popularCategories) && $popularCategories->count() > 0)
        <div class="mt-12 bg-gray-50 rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">인기 카테고리</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($popularCategories as $popular)
                    <a href="{{ route('categories.show', $popular) }}" 
                       class="p-4 bg-white rounded-md hover:shadow-sm transition duration-200">
                        <h3 class="font-medium text-gray-900">{{ $popular->name }}</h3>
                        <div class="text-sm text-gray-500 mt-1">
                            {{ $popular->posts_count ?? 0 }}개 포스트
                        </div>
                        @if($popular->description)
                            <p class="text-xs text-gray-400 mt-1 line-clamp-2">{{ $popular->description }}</p>
                        @endif
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

// 정렬/타입 변경시 자동 제출
document.getElementById('sort').addEventListener('change', function() {
    document.querySelector('form').submit();
});

document.getElementById('type').addEventListener('change', function() {
    document.querySelector('form').submit();
});
</script>
@endpush
@endsection