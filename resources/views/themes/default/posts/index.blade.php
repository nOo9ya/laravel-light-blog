@extends('themes.default.layouts.app')

@section('title', '블로그 포스트')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- 헤더 -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">블로그 포스트</h1>
            <p class="text-lg text-gray-600">최신 포스트와 이야기들을 만나보세요</p>
        </div>

        <!-- 필터링 (선택사항) -->
        <div class="mb-8">
            <div class="flex flex-wrap gap-4 justify-center">
                <a href="{{ route('posts.index') }}" 
                   class="px-4 py-2 rounded-full {{ !request('category') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} transition duration-200">
                    전체
                </a>
                @foreach(\App\Models\Category::forPosts()->active()->ordered()->get() as $category)
                <a href="{{ route('posts.index', ['category' => $category->slug]) }}" 
                   class="px-4 py-2 rounded-full {{ request('category') == $category->slug ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} transition duration-200">
                    {{ $category->name }}
                </a>
                @endforeach
            </div>
        </div>

        <!-- 포스트 목록 -->
        @if($posts->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($posts as $post)
            <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <!-- 대표 이미지 -->
                @if($post->main_image)
                <div class="aspect-w-16 aspect-h-9">
                    <img src="{{ asset('storage/' . $post->main_image) }}" 
                         alt="{{ $post->title }}" 
                         class="w-full h-48 object-cover">
                </div>
                @else
                <div class="w-full h-48 bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                    <svg class="w-16 h-16 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                @endif

                <div class="p-6">
                    <!-- 카테고리 -->
                    @if($post->category)
                    <div class="mb-3">
                        <span class="inline-block px-3 py-1 text-xs font-semibold text-blue-600 bg-blue-100 rounded-full">
                            {{ $post->category->name }}
                        </span>
                    </div>
                    @endif

                    <!-- 제목 -->
                    <h2 class="text-xl font-bold text-gray-800 mb-3 line-clamp-2">
                        <a href="{{ route('posts.show', $post->slug) }}" class="hover:text-blue-600 transition duration-200">
                            {{ $post->title }}
                        </a>
                    </h2>

                    <!-- 요약 -->
                    <p class="text-gray-600 mb-4 line-clamp-3">
                        {{ $post->excerpt }}
                    </p>

                    <!-- 메타 정보 -->
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <div class="flex items-center space-x-4">
                            <span>{{ $post->user->name }}</span>
                            <span>{{ $post->published_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                {{ number_format($post->views_count) }}
                            </span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $post->reading_time }}분
                            </span>
                        </div>
                    </div>

                    <!-- 태그 -->
                    @if($post->tags->count() > 0)
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($post->tags->take(3) as $tag)
                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">
                            #{{ $tag->name }}
                        </span>
                        @endforeach
                        @if($post->tags->count() > 3)
                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">
                            +{{ $post->tags->count() - 3 }}
                        </span>
                        @endif
                    </div>
                    @endif
                </div>
            </article>
            @endforeach
        </div>

        <!-- 페이지네이션 -->
        @if($posts->hasPages())
        <div class="mt-12 flex justify-center">
            {{ $posts->links() }}
        </div>
        @endif

        @else
        <!-- 포스트가 없을 때 -->
        <div class="text-center py-16">
            <svg class="mx-auto h-24 w-24 text-gray-400 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-2xl font-semibold text-gray-800 mb-2">아직 포스트가 없습니다</h3>
            <p class="text-gray-600 mb-8">새로운 포스트를 기다려주세요!</p>
            <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition duration-200">
                홈으로 돌아가기
            </a>
        </div>
        @endif
    </div>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection