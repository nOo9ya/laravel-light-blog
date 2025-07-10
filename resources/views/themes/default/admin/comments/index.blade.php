@extends('themes.default.layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- 관리자 네비게이션 -->
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex space-x-8">
                        <a href="{{ route('admin.dashboard') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            대시보드
                        </a>
                        <a href="{{ route('admin.posts.create') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            포스트 관리
                        </a>
                        <a href="#" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            카테고리 관리
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            사용자 관리
                        </a>
                        <a href="{{ route('admin.comments.index') }}" class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            댓글 관리
                        </a>
                        <a href="{{ route('admin.settings.index') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            시스템 설정
                        </a>
                        <a href="#" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            테마 관리
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                            로그아웃
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- 메인 컨텐츠 -->
    <div class="py-10">
        <header>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-3xl font-bold leading-tight text-gray-900">댓글 관리</h1>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- 필터 및 검색 -->
                <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-5 sm:p-6">
                        <form method="GET" action="{{ route('admin.comments.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- 상태 필터 -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">상태</label>
                                <select name="status" id="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">전체</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>승인 대기</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>승인됨</option>
                                    <option value="spam" {{ request('status') == 'spam' ? 'selected' : '' }}>스팸</option>
                                    <option value="deleted" {{ request('status') == 'deleted' ? 'selected' : '' }}>삭제됨</option>
                                </select>
                            </div>

                            <!-- 포스트 필터 -->
                            <div>
                                <label for="post_id" class="block text-sm font-medium text-gray-700">포스트</label>
                                <select name="post_id" id="post_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">전체 포스트</option>
                                    @foreach($posts as $post)
                                        <option value="{{ $post->id }}" {{ request('post_id') == $post->id ? 'selected' : '' }}>
                                            {{ Str::limit($post->title, 30) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 검색어 -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700">검색어</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                       placeholder="내용, 작성자명 검색..."
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <!-- 검색 버튼 -->
                            <div class="flex items-end">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    검색
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 일괄 처리 -->
                <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-5 sm:p-6">
                        <form id="bulk-action-form" method="POST" action="{{ route('admin.comments.bulk-action') }}">
                            @csrf
                            <div class="flex items-center space-x-4">
                                <select name="action" id="bulk-action" required 
                                        class="block w-40 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">일괄 처리 선택</option>
                                    <option value="approve">승인</option>
                                    <option value="spam">스팸 처리</option>
                                    <option value="delete">삭제</option>
                                </select>
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    선택한 댓글 처리
                                </button>
                                <span class="text-sm text-gray-500">총 {{ $comments->total() }}개 댓글</span>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 댓글 목록 -->
                <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-5 sm:p-6">
                        @if($comments->count() > 0)
                            <div class="space-y-6">
                                @foreach($comments as $comment)
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-start space-x-3">
                                                <!-- 체크박스 -->
                                                <input type="checkbox" name="comment_ids[]" value="{{ $comment->id }}" 
                                                       form="bulk-action-form"
                                                       class="mt-1 focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                
                                                <!-- 댓글 내용 -->
                                                <div class="flex-1 min-w-0">
                                                    <!-- 작성자 정보 -->
                                                    <div class="flex items-center space-x-2 mb-2">
                                                        <span class="text-sm font-medium text-gray-900">{{ $comment->author_name }}</span>
                                                        @if($comment->is_guest)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                비회원
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                회원
                                                            </span>
                                                        @endif
                                                        
                                                        <!-- 상태 -->
                                                        @if($comment->status == 'pending')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                승인 대기
                                                            </span>
                                                        @elseif($comment->status == 'approved')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                승인됨
                                                            </span>
                                                        @elseif($comment->status == 'spam')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                스팸
                                                            </span>
                                                        @elseif($comment->status == 'deleted')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                                삭제됨
                                                            </span>
                                                        @endif
                                                        
                                                        <!-- 대댓글 표시 -->
                                                        @if($comment->depth > 0)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                                {{ $comment->depth }}단계 댓글
                                                            </span>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- 댓글 내용 -->
                                                    <div class="text-sm text-gray-700 mb-2">
                                                        {!! $comment->content_html ?: nl2br(e($comment->content)) !!}
                                                    </div>
                                                    
                                                    <!-- 포스트 정보 -->
                                                    <div class="text-xs text-gray-500 mb-2">
                                                        포스트: <a href="{{ route('posts.show', $comment->post->slug) }}" target="_blank" 
                                                                 class="text-indigo-600 hover:text-indigo-900">{{ $comment->post->title }}</a>
                                                    </div>
                                                    
                                                    <!-- 스팸 점수 -->
                                                    @if($comment->spam_score && $comment->spam_score['score'] > 0)
                                                        <div class="text-xs text-red-600 mb-2">
                                                            스팸 점수: {{ $comment->spam_score['score'] }}점
                                                            @if(!empty($comment->spam_score['factors']))
                                                                ({{ implode(', ', $comment->spam_score['factors']) }})
                                                            @endif
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- 메타 정보 -->
                                                    <div class="text-xs text-gray-500">
                                                        작성일: {{ $comment->created_at->format('Y-m-d H:i') }}
                                                        @if($comment->ip_address)
                                                            | IP: {{ $comment->ip_address }}
                                                        @endif
                                                        @if($comment->author_email)
                                                            | 이메일: {{ $comment->author_email }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- 개별 액션 -->
                                            <div class="flex items-center space-x-2">
                                                @if($comment->status == 'pending')
                                                    <form method="POST" action="{{ route('admin.comments.approve', $comment->id) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                            승인
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                @if($comment->status != 'spam')
                                                    <form method="POST" action="{{ route('admin.comments.spam', $comment->id) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                                            스팸
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                <form method="POST" action="{{ route('admin.comments.destroy', $comment->id) }}" class="inline"
                                                      onsubmit="return confirm('정말 삭제하시겠습니까?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                        삭제
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- 페이지네이션 -->
                            <div class="mt-6">
                                {{ $comments->appends(request()->query())->links() }}
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">댓글이 없습니다</h3>
                                <p class="mt-1 text-sm text-gray-500">현재 조건에 맞는 댓글이 없습니다.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 전체 선택/해제
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('input[name="comment_ids[]"]');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    // 일괄 처리 확인
    const bulkForm = document.getElementById('bulk-action-form');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            const checkedBoxes = document.querySelectorAll('input[name="comment_ids[]"]:checked');
            const action = document.getElementById('bulk-action').value;
            
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('처리할 댓글을 선택해주세요.');
                return;
            }
            
            if (!action) {
                e.preventDefault();
                alert('처리할 작업을 선택해주세요.');
                return;
            }
            
            const actionNames = {
                'approve': '승인',
                'spam': '스팸 처리',
                'delete': '삭제'
            };
            
            if (!confirm(`선택한 ${checkedBoxes.length}개 댓글을 ${actionNames[action]}하시겠습니까?`)) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endsection