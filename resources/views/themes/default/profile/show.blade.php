@extends('themes.default.layouts.app')

@section('title', $user->name . '님의 프로필')
@section('meta_description', $user->name . '님이 작성한 포스트와 활동 내역을 확인하세요.')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- 프로필 헤더 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
            <div class="flex flex-col md:flex-row items-start md:items-center">
                <!-- 프로필 이미지 -->
                <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-8">
                    <div class="w-32 h-32 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-4xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                </div>
                
                <!-- 프로필 정보 -->
                <div class="flex-1">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $user->name }}</h1>
                            <div class="flex items-center space-x-4 text-sm text-gray-600 mb-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $user->role === 'admin' ? '관리자' : '작성자' }}
                                </span>
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center text-green-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        인증된 사용자
                                    </span>
                                @endif
                                <span>{{ $user->created_at->format('Y년 m월') }} 가입</span>
                            </div>
                        </div>
                        
                        @auth
                            @if(auth()->id() === $user->id)
                                <div class="flex space-x-2">
                                    <a href="{{ route('profile.edit') }}" 
                                       class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition duration-200">
                                        프로필 수정
                                    </a>
                                    @if($user->role === 'admin' || $user->role === 'author')
                                        <a href="{{ route('admin.dashboard') }}" 
                                           class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition duration-200">
                                            관리자 페이지
                                        </a>
                                    @endif
                                </div>
                            @endif
                        @endauth
                    </div>
                    
                    <!-- 프로필 설명 (향후 추가 예정) -->
                    @if(isset($user->bio) && $user->bio)
                        <p class="text-gray-700 leading-relaxed">{{ $user->bio }}</p>
                    @else
                        <p class="text-gray-500 italic">아직 자기소개를 작성하지 않았습니다.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- 통계 카드 -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['posts'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">작성한 포스트</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $stats['comments'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">작성한 댓글</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $stats['total_views'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">총 조회수</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $stats['avg_views'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">평균 조회수</div>
            </div>
        </div>

        <!-- 탭 네비게이션 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button onclick="showTab('posts')" 
                            id="tab-posts"
                            class="tab-button py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap border-indigo-500 text-indigo-600">
                        포스트
                    </button>
                    <button onclick="showTab('comments')" 
                            id="tab-comments"
                            class="tab-button py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        댓글
                    </button>
                    <button onclick="showTab('activity')" 
                            id="tab-activity"
                            class="tab-button py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        활동 내역
                    </button>
                </nav>
            </div>
        </div>

        <!-- 포스트 탭 -->
        <div id="content-posts" class="tab-content">
            @if($posts && $posts->count() > 0)
                <div class="space-y-6">
                    @foreach($posts as $post)
                        <article class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition duration-200">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h2 class="text-xl font-semibold text-gray-900 mb-2">
                                        <a href="{{ route('posts.show', $post) }}" class="hover:text-indigo-600">
                                            {{ $post->title }}
                                        </a>
                                    </h2>
                                    
                                    <div class="flex items-center text-sm text-gray-500 mb-3">
                                        <time datetime="{{ $post->published_at->format('Y-m-d') }}">
                                            {{ $post->published_at->format('Y년 m월 d일') }}
                                        </time>
                                        @if($post->category)
                                            <span class="mx-2">•</span>
                                            <a href="{{ route('categories.show', $post->category) }}" 
                                               class="text-indigo-600 hover:text-indigo-800">
                                                {{ $post->category->name }}
                                            </a>
                                        @endif
                                        <span class="mx-2">•</span>
                                        <span>조회 {{ number_format($post->views_count) }}</span>
                                        @if($post->comments_count > 0)
                                            <span class="mx-2">•</span>
                                            <span>댓글 {{ $post->comments_count }}</span>
                                        @endif
                                    </div>
                                    
                                    @if($post->summary)
                                        <p class="text-gray-600 line-clamp-2 mb-4">{{ $post->summary }}</p>
                                    @endif
                                    
                                    <!-- 태그 -->
                                    @if($post->tags->count() > 0)
                                        <div class="flex flex-wrap gap-2">
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
                                </div>
                                
                                @if($post->main_image)
                                    <div class="ml-6 flex-shrink-0">
                                        <img src="{{ asset($post->main_image) }}" 
                                             alt="{{ $post->title }}" 
                                             class="w-24 h-24 object-cover rounded-lg">
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
                
                <!-- 페이지네이션 -->
                @if($posts->hasPages())
                    <div class="flex justify-center mt-8">
                        {{ $posts->links() }}
                    </div>
                @endif
            @else
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">작성한 포스트가 없습니다</h3>
                    <p class="text-gray-500">아직 작성한 포스트가 없습니다.</p>
                </div>
            @endif
        </div>

        <!-- 댓글 탭 -->
        <div id="content-comments" class="tab-content hidden">
            @if($comments && $comments->count() > 0)
                <div class="space-y-4">
                    @foreach($comments as $comment)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center text-sm text-gray-500 mb-2">
                                        <time datetime="{{ $comment->created_at->format('Y-m-d') }}">
                                            {{ $comment->created_at->format('Y년 m월 d일') }}
                                        </time>
                                        <span class="mx-2">•</span>
                                        <a href="{{ route('posts.show', $comment->post) }}" 
                                           class="text-indigo-600 hover:text-indigo-800">
                                            {{ $comment->post->title }}
                                        </a>
                                        @if($comment->parent_id)
                                            <span class="mx-2">•</span>
                                            <span class="text-orange-600">답글</span>
                                        @endif
                                    </div>
                                    
                                    <div class="prose prose-sm max-w-none text-gray-700">
                                        {!! $comment->content_html ?? nl2br(e($comment->content)) !!}
                                    </div>
                                </div>
                                
                                <span class="ml-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $comment->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                       ($comment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $comment->status === 'approved' ? '승인됨' : 
                                       ($comment->status === 'pending' ? '대기중' : '거부됨') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- 페이지네이션 -->
                @if($comments->hasPages())
                    <div class="flex justify-center mt-8">
                        {{ $comments->links() }}
                    </div>
                @endif
            @else
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">작성한 댓글이 없습니다</h3>
                    <p class="text-gray-500">아직 작성한 댓글이 없습니다.</p>
                </div>
            @endif
        </div>

        <!-- 활동 내역 탭 -->
        <div id="content-activity" class="tab-content hidden">
            @if($activities && $activities->count() > 0)
                <div class="space-y-4">
                    @foreach($activities as $activity)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($activity['type'] === 'post_created')
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                    @elseif($activity['type'] === 'comment_created')
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $activity['description'] }}
                                        </p>
                                        <time class="text-xs text-gray-500">
                                            {{ $activity['created_at']->diffForHumans() }}
                                        </time>
                                    </div>
                                    @if(isset($activity['link']))
                                        <a href="{{ $activity['link'] }}" 
                                           class="text-sm text-indigo-600 hover:text-indigo-800">
                                            자세히 보기 →
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">활동 내역이 없습니다</h3>
                    <p class="text-gray-500">아직 활동 내역이 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// 탭 전환 기능
function showTab(tabName) {
    // 모든 탭 버튼 비활성화
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-indigo-500', 'text-indigo-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // 모든 탭 내용 숨기기
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // 선택된 탭 활성화
    document.getElementById(`tab-${tabName}`).classList.remove('border-transparent', 'text-gray-500');
    document.getElementById(`tab-${tabName}`).classList.add('border-indigo-500', 'text-indigo-600');
    
    // 선택된 탭 내용 보이기
    document.getElementById(`content-${tabName}`).classList.remove('hidden');
    
    // URL 해시 업데이트
    window.history.replaceState(null, null, `#${tabName}`);
}

// 페이지 로드 시 URL 해시에 따라 탭 활성화
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash.substring(1);
    const validTabs = ['posts', 'comments', 'activity'];
    
    if (validTabs.includes(hash)) {
        showTab(hash);
    } else {
        showTab('posts');
    }
});
</script>
@endpush
@endsection