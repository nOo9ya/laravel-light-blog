<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 테스트 목적: 회원 댓글 생성 시 바로 승인되는지 검증
     * 테스트 시나리오: 로그인한 회원이 댓글을 작성한 경우
     * 기대 결과: 댓글이 바로 approved 상태로 생성됨
     * 관련 비즈니스 규칙: 회원 댓글 자동 승인 정책
     */
    public function test_회원_댓글_생성시_자동_승인()
    {
        // Given: 회원과 포스트 준비
        $user = User::factory()->create();
        $post = Post::factory()->create();

        // When: 회원이 댓글 작성
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => '회원이 작성한 댓글입니다.',
        ]);

        // Then: 댓글이 바로 승인됨
        expect($comment->status)->toBe('approved');
        expect($comment->is_approved)->toBeTrue();
        expect($comment->author_name)->toBe($user->name);
        expect($comment->is_guest)->toBeFalse();
    }

    /**
     * 테스트 목적: 비회원 댓글 생성 시 승인 대기 상태인지 검증
     * 테스트 시나리오: 비회원이 댓글을 작성한 경우
     * 기대 결과: 댓글이 pending 상태로 생성됨
     * 관련 비즈니스 규칙: 비회원 댓글 승인 대기 정책
     */
    public function test_비회원_댓글_생성시_승인_대기()
    {
        // Given: 포스트 준비
        $post = Post::factory()->create();

        // When: 비회원이 댓글 작성
        $comment = Comment::create([
            'post_id' => $post->id,
            'guest_name' => '비회원',
            'guest_email' => 'guest@example.com',
            'guest_password' => 'password123',
            'content' => '비회원이 작성한 댓글입니다.',
        ]);

        // Then: 댓글이 승인 대기 상태
        expect($comment->status)->toBe('pending');
        expect($comment->is_pending)->toBeTrue();
        expect($comment->author_name)->toBe('비회원');
        expect($comment->is_guest)->toBeTrue();
    }

    /**
     * 테스트 목적: 댓글 계층 구조가 올바르게 설정되는지 검증
     * 테스트 시나리오: 대댓글을 작성한 경우
     * 기대 결과: depth와 path가 올바르게 설정됨
     * 관련 비즈니스 규칙: 댓글 계층 구조 관리
     */
    public function test_대댓글_계층_구조_설정()
    {
        // Given: 포스트와 부모 댓글 준비
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $parentComment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => '부모 댓글입니다.',
        ]);

        // When: 대댓글 작성
        $replyComment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
            'content' => '대댓글입니다.',
        ]);

        // Then: 계층 구조가 올바르게 설정됨
        expect($replyComment->depth)->toBe(1);
        expect($replyComment->path)->toBe('/' . $parentComment->id);
        expect($parentComment->has_replies)->toBeTrue();
        expect($parentComment->replies_count)->toBe(1);
    }

    /**
     * 테스트 목적: 스팸 점수 계산이 올바르게 작동하는지 검증
     * 테스트 시나리오: 스팸성 내용이 포함된 댓글 작성
     * 기대 결과: 스팸 점수가 계산되고 높은 점수시 자동 차단
     * 관련 비즈니스 규칙: 스팸 댓글 자동 감지 시스템
     */
    public function test_스팸_점수_계산_및_자동_차단()
    {
        // Given: 포스트 준비
        $post = Post::factory()->create();

        // When: 스팸성 댓글 작성 (스팸 키워드 + 많은 링크)
        $comment = Comment::create([
            'post_id' => $post->id,
            'guest_name' => '스패머',
            'guest_email' => 'spam@example.com',
            'guest_password' => 'password',
            'content' => '무료 돈 바카라 http://spam1.com http://spam2.com http://spam3.com http://spam4.com',
        ]);

        // Then: 스팸 점수가 계산되고 자동 차단됨
        expect($comment->spam_score)->not->toBeNull();
        expect($comment->spam_score['score'])->toBeGreaterThan(0);
        expect($comment->status)->toBe('spam');
        expect($comment->is_spam)->toBeTrue();
    }

    /**
     * 테스트 목적: 링크 감지 및 HTML 변환이 올바르게 작동하는지 검증
     * 테스트 시나리오: 링크가 포함된 댓글 작성
     * 기대 결과: 링크가 감지되고 HTML로 변환됨
     * 관련 비즈니스 규칙: 댓글 내 링크 자동 변환
     */
    public function test_링크_감지_및_HTML_변환()
    {
        // Given: 포스트 준비
        $post = Post::factory()->create();

        // When: 링크가 포함된 댓글 작성
        $comment = Comment::create([
            'post_id' => $post->id,
            'guest_name' => '사용자',
            'guest_email' => 'user@example.com',
            'guest_password' => 'password',
            'content' => '좋은 글이네요. https://example.com 참고해보세요.',
        ]);

        // Then: 링크가 감지되고 HTML로 변환됨
        expect($comment->detected_links)->toContain('https://example.com');
        expect($comment->content_html)->toContain('<a href="https://example.com"');
        expect($comment->content_html)->toContain('target="_blank"');
        expect($comment->content_html)->toContain('rel="noopener noreferrer"');
    }

    /**
     * 테스트 목적: 댓글 승인 기능이 올바르게 작동하는지 검증
     * 테스트 시나리오: 승인 대기 중인 댓글을 승인한 경우
     * 기대 결과: 댓글 상태가 approved로 변경되고 승인 정보가 기록됨
     * 관련 비즈니스 규칙: 댓글 승인 기능
     */
    public function test_댓글_승인_기능()
    {
        // Given: 승인 대기 중인 댓글과 관리자 준비
        $admin = User::factory()->create(['role' => 'admin']);
        $post = Post::factory()->create();
        $comment = Comment::create([
            'post_id' => $post->id,
            'guest_name' => '비회원',
            'guest_email' => 'guest@example.com',
            'guest_password' => 'password',
            'content' => '승인 대기 중인 댓글',
            'status' => 'pending',
        ]);

        // When: 관리자가 댓글 승인
        $result = $comment->approve($admin);

        // Then: 댓글이 승인됨
        expect($result)->toBeTrue();
        expect($comment->fresh()->status)->toBe('approved');
        expect($comment->fresh()->approved_at)->not->toBeNull();
        expect($comment->fresh()->approved_by)->toBe($admin->id);
    }

    /**
     * 테스트 목적: 비회원 댓글 비밀번호 검증이 올바르게 작동하는지 검증
     * 테스트 시나리오: 비회원이 비밀번호로 댓글 수정/삭제 시도
     * 기대 결과: 올바른 비밀번호일 때만 통과
     * 관련 비즈니스 규칙: 비회원 댓글 보안
     */
    public function test_비회원_댓글_비밀번호_검증()
    {
        // Given: 비회원 댓글 준비
        $post = Post::factory()->create();
        $comment = Comment::create([
            'post_id' => $post->id,
            'guest_name' => '비회원',
            'guest_email' => 'guest@example.com',
            'guest_password' => 'correct_password',
            'content' => '비회원 댓글',
        ]);

        // When & Then: 비밀번호 검증
        expect($comment->verifyGuestPassword('correct_password'))->toBeTrue();
        expect($comment->verifyGuestPassword('wrong_password'))->toBeFalse();
    }

    /**
     * 테스트 목적: 포스트-댓글 관계가 올바르게 설정되는지 검증
     * 테스트 시나리오: 포스트에 여러 댓글이 있는 경우
     * 기대 결과: 포스트에서 승인된 댓글만 조회됨
     * 관련 비즈니스 규칙: 포스트-댓글 관계 및 승인된 댓글만 표시
     */
    public function test_포스트_댓글_관계_및_승인된_댓글만_표시()
    {
        // Given: 포스트와 다양한 상태의 댓글들 준비
        $user = User::factory()->create();
        $post = Post::factory()->create();
        
        $approvedComment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => '승인된 댓글',
            'status' => 'approved',
        ]);
        
        $pendingComment = Comment::create([
            'post_id' => $post->id,
            'guest_name' => '비회원',
            'guest_email' => 'guest@example.com',
            'guest_password' => 'password',
            'content' => '승인 대기 댓글',
            'status' => 'pending',
        ]);
        
        $spamComment = Comment::create([
            'post_id' => $post->id,
            'guest_name' => '스패머',
            'guest_email' => 'spam@example.com',
            'guest_password' => 'password',
            'content' => '스팸 댓글',
            'status' => 'spam',
        ]);

        // When: 포스트의 승인된 댓글 조회
        $approvedComments = $post->approvedComments;
        $topLevelComments = $post->topLevelComments;

        // Then: 승인된 댓글만 반환됨
        expect($post->comments()->count())->toBe(3); // 전체 댓글
        expect($approvedComments->count())->toBe(1); // 승인된 댓글만
        expect($topLevelComments->count())->toBe(1); // 최상위 승인된 댓글만
        expect($approvedComments->first()->id)->toBe($approvedComment->id);
    }

    /**
     * 테스트 목적: 댓글 스코프들이 올바르게 작동하는지 검증
     * 테스트 시나리오: 다양한 스코프를 사용한 댓글 조회
     * 기대 결과: 각 스코프가 올바른 결과를 반환함
     * 관련 비즈니스 규칙: 댓글 필터링 및 정렬
     */
    public function test_댓글_스코프_기능()
    {
        // Given: 포스트와 다양한 댓글들 준비
        $user = User::factory()->create();
        $post = Post::factory()->create();
        
        $parentComment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => '부모 댓글',
            'status' => 'approved',
        ]);
        
        $replyComment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
            'content' => '대댓글',
            'status' => 'approved',
        ]);
        
        $pendingComment = Comment::create([
            'post_id' => $post->id,
            'guest_name' => '비회원',
            'guest_email' => 'guest@example.com',
            'guest_password' => 'password',
            'content' => '승인 대기',
            'status' => 'pending',
        ]);

        // When & Then: 각 스코프 테스트
        expect(Comment::approved()->count())->toBe(2);
        expect(Comment::pending()->count())->toBe(1);
        expect(Comment::topLevel()->count())->toBe(2); // 부모 댓글 + 승인 대기 댓글
        expect(Comment::where('parent_id', '!=', null)->count())->toBe(1);
        expect(Comment::byPost($post->id)->count())->toBe(3);
    }
}
