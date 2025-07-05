<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Http\Resources\CommentResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['adminIndex', 'approve', 'markAsSpam', 'destroy']);
    }

    /**
     * 포스트의 댓글 목록 조회 (AJAX용)
     */
    public function index(Post $post): JsonResponse
    {
        $comments = $post->topLevelComments()
            ->with(['user', 'replies.user', 'replies.replies'])
            ->get();

        return response()->json([
            'success' => true,
            'comments' => $comments,
            'count' => $post->approvedComments()->count(),
        ]);
    }

    /**
     * 댓글 작성
     */
    public function store(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|min:1|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
            
            // 비회원 정보 (로그인하지 않은 경우)
            'guest_name' => 'required_without:user_id|string|max:50',
            'guest_email' => 'required_without:user_id|email|max:100',
            'guest_password' => 'required_without:user_id|string|min:4|max:20',
        ]);

        try {
            $commentData = [
                'post_id' => $post->id,
                'content' => $validated['content'],
                'parent_id' => $validated['parent_id'] ?? null,
            ];

            // 로그인한 사용자
            if (Auth::check()) {
                $commentData['user_id'] = Auth::id();
                $commentData['status'] = 'approved'; // 회원은 바로 승인
            } else {
                // 비회원
                $commentData['guest_name'] = $validated['guest_name'];
                $commentData['guest_email'] = $validated['guest_email'];
                $commentData['guest_password'] = $validated['guest_password'];
                $commentData['status'] = 'pending'; // 비회원은 승인 대기
            }

            $comment = Comment::create($commentData);

            // 댓글이 생성되면 관계 로드하여 반환
            $comment->load(['user', 'parent']);

            return response()->json([
                'success' => true,
                'message' => Auth::check() ? '댓글이 등록되었습니다.' : '댓글이 등록되었습니다. 관리자 승인 후 표시됩니다.',
                'comment' => $comment,
                'requires_approval' => !Auth::check(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '댓글 등록 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 비회원 댓글 수정
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        // 비회원 댓글만 수정 가능
        if (!$comment->is_guest) {
            return response()->json([
                'success' => false,
                'message' => '회원 댓글은 수정할 수 없습니다.',
            ], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|min:1|max:1000',
            'guest_password' => 'required|string',
        ]);

        // 비밀번호 확인
        if (!$comment->verifyGuestPassword($validated['guest_password'])) {
            return response()->json([
                'success' => false,
                'message' => '비밀번호가 올바르지 않습니다.',
            ], 403);
        }

        try {
            $comment->update([
                'content' => $validated['content'],
                'status' => 'pending', // 수정 시 다시 승인 대기
            ]);

            return response()->json([
                'success' => true,
                'message' => '댓글이 수정되었습니다. 관리자 승인 후 표시됩니다.',
                'comment' => $comment,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '댓글 수정 중 오류가 발생했습니다.',
            ], 500);
        }
    }

    /**
     * 비회원 댓글 삭제
     */
    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        // 관리자가 아닌 경우 비회원 댓글만 삭제 가능
        if (!Auth::check() || !Auth::user()->hasRole('admin')) {
            if (!$comment->is_guest) {
                return response()->json([
                    'success' => false,
                    'message' => '회원 댓글은 삭제할 수 없습니다.',
                ], 403);
            }

            $validated = $request->validate([
                'guest_password' => 'required|string',
            ]);

            // 비밀번호 확인
            if (!$comment->verifyGuestPassword($validated['guest_password'])) {
                return response()->json([
                    'success' => false,
                    'message' => '비밀번호가 올바르지 않습니다.',
                ], 403);
            }
        }

        try {
            // 대댓글이 있는 경우 내용만 삭제 표시
            if ($comment->children()->exists()) {
                $comment->update([
                    'content' => '[삭제된 댓글입니다]',
                    'status' => 'deleted',
                ]);
                $message = '댓글이 삭제되었습니다.';
            } else {
                // 대댓글이 없으면 완전 삭제
                $comment->delete();
                $message = '댓글이 완전히 삭제되었습니다.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '댓글 삭제 중 오류가 발생했습니다.',
            ], 500);
        }
    }

    /**
     * 관리자 댓글 관리 페이지
     */
    public function adminIndex(Request $request): View
    {
        $query = Comment::with(['post:id,title,slug', 'user:id,name', 'parent'])
            ->orderBy('created_at', 'desc');

        // 상태 필터
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 포스트 필터
        if ($request->filled('post_id')) {
            $query->where('post_id', $request->post_id);
        }

        // 검색
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhere('guest_name', 'like', "%{$search}%")
                  ->orWhere('guest_email', 'like', "%{$search}%");
            });
        }

        $comments = $query->paginate(20);
        
        // 포스트 목록 (필터용)
        $posts = Post::select('id', 'title')->orderBy('title')->get();

        return view('themes.default.admin.comments.index', compact('comments', 'posts'));
    }

    /**
     * 댓글 승인
     */
    public function approve(Comment $comment): JsonResponse
    {
        try {
            $comment->approve();

            return response()->json([
                'success' => true,
                'message' => '댓글이 승인되었습니다.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '댓글 승인 중 오류가 발생했습니다.',
            ], 500);
        }
    }

    /**
     * 댓글을 스팸으로 표시
     */
    public function markAsSpam(Comment $comment): JsonResponse
    {
        try {
            $comment->markAsSpam();

            return response()->json([
                'success' => true,
                'message' => '댓글이 스팸으로 분류되었습니다.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '스팸 처리 중 오류가 발생했습니다.',
            ], 500);
        }
    }

    /**
     * 댓글 일괄 처리
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,spam,delete',
            'comment_ids' => 'required|array',
            'comment_ids.*' => 'exists:comments,id',
        ]);

        try {
            $comments = Comment::whereIn('id', $validated['comment_ids']);
            
            switch ($validated['action']) {
                case 'approve':
                    $comments->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => Auth::id(),
                    ]);
                    $message = '선택한 댓글들이 승인되었습니다.';
                    break;
                    
                case 'spam':
                    $comments->update(['status' => 'spam']);
                    $message = '선택한 댓글들이 스팸으로 분류되었습니다.';
                    break;
                    
                case 'delete':
                    $comments->delete();
                    $message = '선택한 댓글들이 삭제되었습니다.';
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '일괄 처리 중 오류가 발생했습니다.',
            ], 500);
        }
    }

    /**
     * API: 포스트의 댓글 목록 조회
     */
    public function apiIndex(Post $post, Request $request): JsonResponse
    {
        $query = $post->comments()
            ->approved()
            ->whereNull('parent_id')
            ->with(['user', 'children' => function ($q) {
                $q->approved()->with('user');
            }])
            ->latest();

        $comments = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => CommentResource::collection($comments),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
            ]
        ]);
    }

    /**
     * API: 댓글 스레드 조회
     */
    public function thread(Comment $comment): JsonResponse
    {
        $comment->load([
            'children' => function ($query) {
                $query->approved()->with('user')->latest();
            },
            'user'
        ]);

        return response()->json([
            'success' => true,
            'data' => new CommentResource($comment)
        ]);
    }

    /**
     * API: 형제 댓글 조회
     */
    public function siblings(Comment $comment): JsonResponse
    {
        $siblings = Comment::approved()
            ->where('post_id', $comment->post_id)
            ->where('parent_id', $comment->parent_id)
            ->where('id', '!=', $comment->id)
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => CommentResource::collection($siblings)
        ]);
    }

    /**
     * 댓글 답글 폼 표시
     */
    public function reply(Comment $comment): View
    {
        return view(themed('comments.reply'), compact('comment'));
    }

    /**
     * 댓글 신고
     */
    public function report(Request $request, Comment $comment): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|in:spam,inappropriate,offensive,other',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            // 이미 신고된 댓글인지 확인 (IP 기반)
            $userIp = $request->ip();
            $alreadyReported = $comment->reports()
                ->where('reporter_ip', $userIp)
                ->exists();

            if ($alreadyReported) {
                return response()->json([
                    'success' => false,
                    'message' => '이미 신고한 댓글입니다.'
                ], 409);
            }

            // 신고 기록 생성
            $comment->reports()->create([
                'reason' => $validated['reason'],
                'description' => $validated['description'],
                'reporter_ip' => $userIp,
                'reporter_user_agent' => $request->userAgent(),
            ]);

            // 신고 횟수가 일정 수준 이상이면 자동으로 검토 대기 상태로 변경
            $reportCount = $comment->reports()->count();
            if ($reportCount >= 3 && $comment->status === 'approved') {
                $comment->update(['status' => 'pending']);
            }

            return response()->json([
                'success' => true,
                'message' => '댓글이 신고되었습니다. 관리자가 검토 후 조치하겠습니다.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '신고 처리 중 오류가 발생했습니다.'
            ], 500);
        }
    }
}
