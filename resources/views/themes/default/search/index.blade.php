@extends('themes.default.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- 검색 헤더 -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">검색 결과</h1>
            <p class="text-gray-600">
                '<strong>{{ $query }}</strong>' 에 대한 검색 결과 {{ $total }}개
            </p>
        </div>

        @if($total > 0)
            <!-- 검색 필터 -->
            <div class="mb-6">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['type' => 'all']) }}" 
                       class="px-4 py-2 rounded-lg text-sm font-medium {{ $type === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                        전체
                    </a>
                    @if(isset($results['posts']) && $results['posts']->total() > 0)
                        <a href="{{ request()->fullUrlWithQuery(['type' => 'post']) }}" 
                           class="px-4 py-2 rounded-lg text-sm font-medium {{ $type === 'post' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            포스트 ({{ $results['posts']->total() }})
                        </a>
                    @endif
                    @if(isset($results['pages']) && $results['pages']->total() > 0)
                        <a href="{{ request()->fullUrlWithQuery(['type' => 'page']) }}" 
                           class="px-4 py-2 rounded-lg text-sm font-medium {{ $type === 'page' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            페이지 ({{ $results['pages']->total() }})
                        </a>
                    @endif
                    @if(isset($results['categories']) && $results['categories']->total() > 0)
                        <a href="{{ request()->fullUrlWithQuery(['type' => 'category']) }}" 
                           class="px-4 py-2 rounded-lg text-sm font-medium {{ $type === 'category' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            카테고리 ({{ $results['categories']->total() }})
                        </a>
                    @endif
                    @if(isset($results['tags']) && $results['tags']->total() > 0)
                        <a href="{{ request()->fullUrlWithQuery(['type' => 'tag']) }}" 
                           class="px-4 py-2 rounded-lg text-sm font-medium {{ $type === 'tag' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            태그 ({{ $results['tags']->total() }})
                        </a>
                    @endif
                </div>
            </div>

            <!-- 검색 결과 -->
            <div class="space-y-8">
                <!-- 포스트 결과 -->
                @if(isset($results['posts']) && $results['posts']->count() > 0)
                    <div>
                        @if($type === 'all')
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">포스트</h2>
                        @endif
                        <div class="space-y-4">
                            @foreach($results['posts'] as $post)
                                <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                    <h3 class="text-lg font-semibold text-blue-600 mb-2">
                                        <a href="{{ route('posts.show', $post->slug) }}" class="hover:underline">
                                            {{ $post->title }}
                                        </a>
                                    </h3>
                                    <p class="text-gray-600 mb-3">
                                        {{ Str::limit($post->summary ?: strip_tags($post->content), 150) }}
                                    </p>
                                    <div class="flex items-center text-sm text-gray-500">
                                        <span>{{ $post->user->name }}</span>
                                        <span class="mx-2">•</span>
                                        <span>{{ $post->created_at->format('Y-m-d') }}</span>
                                        @if($post->category)
                                            <span class="mx-2">•</span>
                                            <span>{{ $post->category->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($type === 'post' || $type === 'all')
                            <div class="mt-6">
                                {{ $results['posts']->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                @endif

                <!-- 페이지 결과 -->
                @if(isset($results['pages']) && $results['pages']->count() > 0)
                    <div>
                        @if($type === 'all')
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">페이지</h2>
                        @endif
                        <div class="space-y-4">
                            @foreach($results['pages'] as $page)
                                <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                    <h3 class="text-lg font-semibold text-green-600 mb-2">
                                        <a href="{{ route('pages.show', $page->slug) }}" class="hover:underline">
                                            {{ $page->title }}
                                        </a>
                                    </h3>
                                    <p class="text-gray-600 mb-3">
                                        {{ Str::limit($page->excerpt ?: strip_tags($page->content), 150) }}
                                    </p>
                                    <div class="flex items-center text-sm text-gray-500">
                                        <span>페이지</span>
                                        <span class="mx-2">•</span>
                                        <span>{{ $page->created_at->format('Y-m-d') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($type === 'page' || $type === 'all')
                            <div class="mt-6">
                                {{ $results['pages']->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                @endif

                <!-- 카테고리 결과 -->
                @if(isset($results['categories']) && $results['categories']->count() > 0)
                    <div>
                        @if($type === 'all')
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">카테고리</h2>
                        @endif
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($results['categories'] as $category)
                                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <h3 class="text-lg font-semibold text-purple-600 mb-2">
                                        <a href="{{ route('categories.show', $category->slug) }}" class="hover:underline">
                                            {{ $category->name }}
                                        </a>
                                    </h3>
                                    @if($category->description)
                                        <p class="text-gray-600 mb-2">{{ $category->description }}</p>
                                    @endif
                                    <div class="text-sm text-gray-500">
                                        {{ $category->posts_count }}개 포스트
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($type === 'category' || $type === 'all')
                            <div class="mt-6">
                                {{ $results['categories']->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                @endif

                <!-- 태그 결과 -->
                @if(isset($results['tags']) && $results['tags']->count() > 0)
                    <div>
                        @if($type === 'all')
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">태그</h2>
                        @endif
                        <div class="flex flex-wrap gap-3">
                            @foreach($results['tags'] as $tag)
                                <a href="{{ route('tags.show', $tag->slug) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-orange-100 text-orange-800 rounded-full hover:bg-orange-200 transition-colors">
                                    <span class="font-medium">{{ $tag->name }}</span>
                                    <span class="ml-2 text-sm">({{ $tag->posts_count }})</span>
                                </a>
                            @endforeach
                        </div>
                        @if($type === 'tag' || $type === 'all')
                            <div class="mt-6">
                                {{ $results['tags']->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @else
            <!-- 검색 결과 없음 -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">검색 결과가 없습니다</h3>
                <p class="mt-1 text-sm text-gray-500">다른 키워드로 검색해보세요.</p>
            </div>
        @endif
    </div>
</div>
@endsection