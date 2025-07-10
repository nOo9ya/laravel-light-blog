<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Models\Analytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 테스트 목적: 포스트 조회시 접속 기록이 생성되는지 검증
     * 테스트 시나리오: 포스트 페이지 방문시
     * 기대 결과: Analytics 테이블에 방문 기록 저장
     * 관련 비즈니스 규칙: 페이지 방문 추적
     */
    public function test_포스트_조회시_접속_기록_생성()
    {
        // Given: 포스트와 사용자 준비
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'status' => 'published',
            'user_id' => $user->id,
        ]);

        // When: 포스트 조회
        $response = $this->get(route('posts.show', $post->slug));

        // Then: 접속 기록이 생성됨
        $response->assertStatus(200);
        $this->assertDatabaseHas('analytics', [
            'post_id' => $post->id,
            'type' => 'page_view',
            'ip_address' => '127.0.0.1',
        ]);
    }

    /**
     * 테스트 목적: 같은 IP에서 중복 방문시 중복 제거 검증
     * 테스트 시나리오: 동일 IP에서 같은 포스트를 여러 번 방문
     * 기대 결과: 지정된 시간 내에는 중복 기록되지 않음
     * 관련 비즈니스 규칙: 중복 방문 방지
     */
    public function test_중복_방문_기록_방지()
    {
        // Given: 포스트와 사용자 준비
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'status' => 'published',
            'user_id' => $user->id,
        ]);

        // When: 같은 포스트를 여러 번 조회
        $this->get(route('posts.show', $post->slug));
        $this->get(route('posts.show', $post->slug));
        $this->get(route('posts.show', $post->slug));

        // Then: 접속 기록은 한 번만 생성됨
        $this->assertEquals(1, Analytics::where('post_id', $post->id)->count());
    }

    /**
     * 테스트 목적: 다양한 브라우저 정보 기록 검증
     * 테스트 시나리오: 다른 User-Agent로 접속
     * 기대 결과: User-Agent, 리퍼러 정보가 올바르게 기록됨
     * 관련 비즈니스 규칙: 방문자 환경 정보 수집
     */
    public function test_브라우저_정보_기록()
    {
        // Given: 포스트 준비
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'status' => 'published',
            'user_id' => $user->id,
        ]);

        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
        $referer = 'https://google.com';

        // When: 특정 User-Agent와 리퍼러로 접속
        $response = $this->withHeaders([
            'User-Agent' => $userAgent,
            'Referer' => $referer,
        ])->get(route('posts.show', $post->slug));

        // Then: 브라우저 정보가 기록됨
        $response->assertStatus(200);
        $this->assertDatabaseHas('analytics', [
            'post_id' => $post->id,
            'user_agent' => $userAgent,
            'referer' => $referer,
        ]);
    }

    /**
     * 테스트 목적: 포스트 조회수 증가 검증
     * 테스트 시나리오: 포스트 방문시 조회수 자동 증가
     * 기대 결과: Post 모델의 views_count 필드가 증가
     * 관련 비즈니스 규칙: 조회수 카운팅
     */
    public function test_포스트_조회수_증가()
    {
        // Given: 포스트 준비
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'status' => 'published',
            'user_id' => $user->id,
            'views_count' => 0,
        ]);

        // When: 포스트 조회
        $this->get(route('posts.show', $post->slug));

        // Then: 조회수가 증가함
        $post->refresh();
        $this->assertEquals(1, $post->views_count);
    }

    /**
     * 테스트 목적: 검색 기록 저장 검증
     * 테스트 시나리오: 검색 수행시
     * 기대 결과: 검색어와 결과 수가 기록됨
     * 관련 비즈니스 규칙: 검색 통계 수집
     */
    public function test_검색_기록_저장()
    {
        // Given: 검색 가능한 포스트 준비
        $user = User::factory()->create();
        Post::factory()->create([
            'title' => 'Laravel 튜토리얼',
            'status' => 'published',
            'user_id' => $user->id,
        ]);

        // When: 검색 수행
        $response = $this->get('/search?q=Laravel');

        // Then: 검색 기록이 저장됨
        $response->assertStatus(200);
        $this->assertDatabaseHas('analytics', [
            'type' => 'search',
            'search_query' => 'Laravel',
            'search_results_count' => 1,
        ]);
    }

    /**
     * 테스트 목적: 일일 통계 조회 기능 검증
     * 테스트 시나리오: 특정 날짜의 통계 조회
     * 기대 결과: 해당 날짜의 방문 기록만 반환
     * 관련 비즈니스 규칙: 일별 통계 제공
     */
    public function test_일일_통계_조회()
    {
        // Given: 여러 날짜의 접속 기록 생성
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'status' => 'published',
            'user_id' => $user->id,
        ]);

        Analytics::factory()->create([
            'post_id' => $post->id,
            'type' => 'page_view',
            'created_at' => now()->subDay(),
        ]);

        Analytics::factory()->create([
            'post_id' => $post->id,
            'type' => 'page_view',
            'created_at' => now(),
        ]);

        // When: 오늘 통계 조회
        $todayStats = Analytics::daily(now()->format('Y-m-d'))->count();

        // Then: 오늘 기록만 반환됨
        $this->assertEquals(1, $todayStats);
    }

    /**
     * 테스트 목적: 인기 포스트 조회 기능 검증
     * 테스트 시나리오: 조회수 기준 인기 포스트 조회
     * 기대 결과: 조회수가 높은 순서로 정렬된 포스트 반환
     * 관련 비즈니스 규칙: 인기 콘텐츠 분석
     */
    public function test_인기_포스트_조회()
    {
        // Given: 조회수가 다른 포스트들 생성
        $user = User::factory()->create();
        
        $post1 = Post::factory()->create([
            'title' => '인기 포스트 1',
            'status' => 'published',
            'user_id' => $user->id,
            'views_count' => 100,
        ]);
        
        $post2 = Post::factory()->create([
            'title' => '인기 포스트 2',
            'status' => 'published',
            'user_id' => $user->id,
            'views_count' => 200,
        ]);
        
        $post3 = Post::factory()->create([
            'title' => '인기 포스트 3',
            'status' => 'published',
            'user_id' => $user->id,
            'views_count' => 50,
        ]);

        // When: 인기 포스트 조회
        $popularPosts = Post::popular()->limit(3)->get();

        // Then: 조회수 순으로 정렬됨
        $this->assertEquals('인기 포스트 2', $popularPosts->first()->title);
        $this->assertEquals('인기 포스트 1', $popularPosts->get(1)->title);
        $this->assertEquals('인기 포스트 3', $popularPosts->last()->title);
    }

    /**
     * 테스트 목적: 리퍼러 분석 기능 검증
     * 테스트 시나리오: 다양한 리퍼러에서 방문
     * 기대 결과: 리퍼러별 방문 통계 제공
     * 관련 비즈니스 규칙: 트래픽 소스 분석
     */
    public function test_리퍼러_분석()
    {
        // Given: 다양한 리퍼러로 접속 기록 생성
        Analytics::factory()->create([
            'type' => 'page_view',
            'referer' => 'https://google.com',
        ]);
        
        Analytics::factory()->create([
            'type' => 'page_view',
            'referer' => 'https://google.com',
        ]);
        
        Analytics::factory()->create([
            'type' => 'page_view',
            'referer' => 'https://naver.com',
        ]);

        // When: 리퍼러별 통계 조회
        $refererStats = Analytics::refererStats()->get();

        // Then: 리퍼러별 카운트가 올바름
        $googleCount = $refererStats->where('referer', 'https://google.com')->first()->count ?? 0;
        $naverCount = $refererStats->where('referer', 'https://naver.com')->first()->count ?? 0;
        
        $this->assertEquals(2, $googleCount);
        $this->assertEquals(1, $naverCount);
    }

    /**
     * 테스트 목적: 관리자 통계 대시보드 접근 검증
     * 테스트 시나리오: 관리자가 통계 페이지 접근
     * 기대 결과: 통계 데이터가 포함된 페이지 반환
     * 관련 비즈니스 규칙: 관리자 통계 대시보드
     */
    public function test_관리자_통계_대시보드_접근()
    {
        // Given: 관리자 사용자 생성
        $admin = User::factory()->create(['role' => 'admin']);
        
        // 접속 기록 생성
        Analytics::factory()->count(5)->create([
            'type' => 'page_view',
            'created_at' => now(),
        ]);

        // When: 관리자로 로그인하여 통계 페이지 접근
        $response = $this->actingAs($admin)->get('/admin/analytics');

        // Then: 통계 페이지가 정상 표시됨
        $response->assertStatus(200);
        $response->assertSee('통계');
        $response->assertSee('5'); // 오늘의 방문자 수
    }
}