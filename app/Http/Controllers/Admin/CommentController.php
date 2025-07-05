<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * 댓글 목록 표시
     */
    public function index(Request $request): View
    {
        $query = Comment::with(['post:id,title,slug', 'user:id,name,email'])
            ->withCount('replies');

        // 상태 필터
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // 검색 필터
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhere('author_name', 'like', "%{$search}%")
                  ->orWhere('author_email', 'like', "%{$search}%")
                  ->orWhereHas('post', function ($postQuery) use ($search) {
                      $postQuery->where('title', 'like', "%{$search}%");
                  });
            });
        }

        // 포스트 필터
        if ($request->filled('post_id')) {
            $query->where('post_id', $request->get('post_id'));
        }

        // 스팸 점수 필터
        if ($request->filled('spam_filter')) {
            $spamLevel = $request->get('spam_filter');
            if ($spamLevel === 'high') {
                $query->where('spam_score', '>=', 70);
            } elseif ($spamLevel === 'medium') {
                $query->whereBetween('spam_score', [30, 69]);
            } elseif ($spamLevel === 'low') {
                $query->where('spam_score', '<', 30);
            }
        }

        // 날짜 필터
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // 정렬
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if ($sortBy === 'spam_score') {
            $query->orderBy('spam_score', $sortDirection)
                  ->orderBy('created_at', 'desc');
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $comments = $query->paginate(20)->withQueryString();
        
        // 통계 정보
        $stats = [
            'total' => Comment::count(),
            'pending' => Comment::where('status', 'pending')->count(),
            'approved' => Comment::where('status', 'approved')->count(),
            'spam' => Comment::where('status', 'spam')->count(),
            'today' => Comment::whereDate('created_at', today())->count(),
        ];

        $posts = Post::select('id', 'title')->orderBy('title')->get();

        return view('admin.comments.index', compact('comments', 'stats', 'posts'));
    }

    /**
     * 댓글 상세 보기
     */
    public function show(Comment $comment): View
    {
        $comment->load(['post:id,title,slug', 'user:id,name,email', 'parent', 'replies.user']);
        
        // 스팸 분석 정보
        $spamAnalysis = [
            'score' => $comment->spam_score,
            'reasons' => $this->getSpamReasons($comment),
            'ip_history' => Comment::where('ip_address', $comment->ip_address)
                ->where('id', '!=', $comment->id)
                ->latest()
                ->limit(5)
                ->get(['id', 'author_name', 'status', 'spam_score', 'created_at']),
        ];

        return view('admin.comments.show', compact('comment', 'spamAnalysis'));
    }

    /**
     * 댓글 수정
     */
    public function update(Request $request, Comment $comment): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string|min:5|max:2000',
            'author_name' => 'required|string|max:100',
            'author_email' => 'required|email|max:255',
            'author_website' => 'nullable|url|max:255',
            'status' => 'required|in:pending,approved,spam',
        ], [
            'content.required' => '댓글 내용을 입력해주세요.',
            'content.min' => '댓글 내용은 최소 5글자 이상이어야 합니다.',
            'content.max' => '댓글 내용은 2000글자를 초과할 수 없습니다.',
            'author_name.required' => '작성자 이름을 입력해주세요.',
            'author_email.required' => '작성자 이메일을 입력해주세요.',
            'author_email.email' => '올바른 이메일 형식을 입력해주세요.',
            'author_website.url' => '올바른 웹사이트 URL을 입력해주세요.',
            'status.required' => '댓글 상태를 선택해주세요.',
        ]);

        $comment->update($request->only([
            'content', 'author_name', 'author_email', 'author_website', 'status'
        ]));

        return redirect()->route('admin.comments.show', $comment)
            ->with('success', '댓글이 수정되었습니다.');
    }

    /**
     * 댓글 삭제
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        $authorName = $comment->author_name;
        
        // 하위 댓글도 함께 삭제
        $comment->replies()->delete();
        $comment->delete();

        return redirect()->route('admin.comments.index')
            ->with('success', "'{$authorName}' 님의 댓글이 삭제되었습니다.");
    }

    /**
     * 댓글 승인
     */
    public function approve(Comment $comment): RedirectResponse
    {
        $comment->update(['status' => 'approved']);

        return redirect()->back()
            ->with('success', '댓글이 승인되었습니다.');
    }

    /**
     * 댓글 거부
     */
    public function reject(Comment $comment): RedirectResponse
    {
        $comment->update(['status' => 'pending']);

        return redirect()->back()
            ->with('success', '댓글이 대기 상태로 변경되었습니다.');
    }

    /**
     * 스팸으로 표시
     */
    public function markAsSpam(Comment $comment): RedirectResponse
    {
        $comment->update([
            'status' => 'spam',
            'spam_score' => max($comment->spam_score, 80) // 최소 80점으로 설정
        ]);

        return redirect()->back()
            ->with('success', '댓글이 스팸으로 표시되었습니다.');
    }

    /**
     * 일괄 작업
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:approve,spam,delete,pending',
            'comment_ids' => 'required|array',
            'comment_ids.*' => 'exists:comments,id',
        ], [
            'action.required' => '작업을 선택해주세요.',
            'action.in' => '올바른 작업을 선택해주세요.',
            'comment_ids.required' => '댓글을 선택해주세요.',
        ]);

        $commentIds = $request->get('comment_ids');
        $action = $request->get('action');
        $count = count($commentIds);

        switch ($action) {
            case 'approve':
                Comment::whereIn('id', $commentIds)->update(['status' => 'approved']);
                $message = "{$count}개 댓글이 승인되었습니다.";
                break;

            case 'spam':
                Comment::whereIn('id', $commentIds)->update([
                    'status' => 'spam',
                    'spam_score' => \DB::raw('GREATEST(spam_score, 80)')
                ]);
                $message = "{$count}개 댓글이 스팸으로 표시되었습니다.";
                break;

            case 'pending':
                Comment::whereIn('id', $commentIds)->update(['status' => 'pending']);
                $message = "{$count}개 댓글이 대기 상태로 변경되었습니다.";
                break;

            case 'delete':
                // 하위 댓글도 함께 삭제
                $comments = Comment::whereIn('id', $commentIds)->get();
                foreach ($comments as $comment) {
                    $comment->replies()->delete();
                    $comment->delete();
                }
                $message = "{$count}개 댓글이 삭제되었습니다.";
                break;

            default:
                return redirect()->route('admin.comments.index')
                    ->with('error', '알 수 없는 작업입니다.');
        }

        return redirect()->route('admin.comments.index')
            ->with('success', $message);
    }

    /**
     * 댓글 데이터 내보내기
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'format' => 'required|in:csv,json',
            'status' => 'nullable|in:pending,approved,spam',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = Comment::with(['post:id,title', 'user:id,name']);

        // 필터 적용
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $comments = $query->orderBy('created_at', 'desc')->get();

        $format = $request->get('format');
        $filename = 'comments_export_' . date('Y-m-d_H-i-s');

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            ];

            $csvData = "ID,포스트 제목,작성자,이메일,내용,상태,스팸점수,작성일\n";
            
            foreach ($comments as $comment) {
                $csvData .= implode(',', [
                    $comment->id,
                    '"' . str_replace('"', '""', $comment->post->title ?? '') . '"',
                    '"' . str_replace('"', '""', $comment->author_name) . '"',
                    '"' . str_replace('"', '""', $comment->author_email) . '"',
                    '"' . str_replace('"', '""', Str::limit($comment->content, 100)) . '"',
                    $comment->status,
                    $comment->spam_score,
                    $comment->created_at->format('Y-m-d H:i:s'),
                ]) . "\n";
            }

            return response($csvData, 200, $headers);

        } else { // JSON
            $data = $comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'post_title' => $comment->post->title ?? null,
                    'author_name' => $comment->author_name,
                    'author_email' => $comment->author_email,
                    'content' => $comment->content,
                    'status' => $comment->status,
                    'spam_score' => $comment->spam_score,
                    'created_at' => $comment->created_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'total' => $data->count(),
                'exported_at' => now()->toISOString(),
            ]);
        }
    }

    /**
     * 내 댓글 목록 (작성자용)
     */
    public function myComments(Request $request): View
    {
        $query = Comment::where('user_id', auth()->id())
            ->with(['post:id,title,slug'])
            ->withCount('replies');

        // 상태 필터
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // 검색 필터
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('content', 'like', "%{$search}%");
        }

        $comments = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.comments.my', compact('comments'));
    }

    /**
     * 스팸 판정 이유 분석
     */
    private function getSpamReasons(Comment $comment): array
    {
        $reasons = [];
        
        // 링크 과다
        $linkCount = substr_count(strtolower($comment->content), 'http');
        if ($linkCount > 2) {
            $reasons[] = "과도한 링크 포함 ({$linkCount}개)";
        }
        
        // 특수문자 과다
        if (preg_match('/[!@#$%^&*()]{3,}/', $comment->content)) {
            $reasons[] = '과도한 특수문자 사용';
        }
        
        // 내용 길이
        if (mb_strlen($comment->content) < 10) {
            $reasons[] = '내용이 너무 짧음';
        }
        
        // 반복 문자
        if (preg_match('/(.)\1{4,}/', $comment->content)) {
            $reasons[] = '반복 문자 과다';
        }
        
        // 대문자 과다
        $upperPercent = (mb_strlen($comment->content) - mb_strlen(preg_replace('/[A-Z]/', '', $comment->content))) / mb_strlen($comment->content) * 100;
        if ($upperPercent > 50) {
            $reasons[] = '대문자 과다 사용';
        }

        return $reasons;
    }
}