<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\SeoMeta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 테스트 목적: 포스트 생성 시 슬러그 자동 생성 검증
     * 테스트 시나리오: 포스트 생성 시 슬러그가 자동으로 생성되는지 확인
     * 기대 결과: 한글 포스트 제목이 영문 슬러그로 변환되어 저장
     * 관련 비즈니스 규칙: 포스트 슬러그 자동 생성 정책
     */
    public function test_포스트_생성시_슬러그_자동_생성()
    {
        // Given: 사용자와 포스트 데이터 준비
        $user = User::factory()->create();
        $postData = [
            'title' => 'Laravel 11 새로운 기능',
            'content' => '<p>Laravel 11에서 추가된 새로운 기능들을 소개합니다.</p>',
            'user_id' => $user->id,
        ];

        // When: 포스트 생성
        $post = Post::create($postData);

        // Then: 슬러그가 자동 생성되었는지 확인
        expect($post->slug)->toBe('Laravel-11-새로운-기능');
        expect($post->title)->toBe('Laravel 11 새로운 기능');
        expect($post->user_id)->toBe($user->id);
    }

    /**
     * 테스트 목적: 포스트 기본 상태 설정 검증
     * 테스트 시나리오: 포스트 생성 시 기본 상태 확인
     * 기대 결과: 기본적으로 draft 상태로 설정
     * 관련 비즈니스 규칙: 포스트 기본 상태 정책
     */
    public function test_포스트_기본_상태_설정()
    {
        // Given: 사용자와 포스트 데이터 준비
        $user = User::factory()->create();
        $postData = [
            'title' => '테스트 포스트',
            'content' => '포스트 내용',
            'user_id' => $user->id,
        ];

        // When: 포스트 생성
        $post = Post::create($postData);

        // Then: 기본 상태 확인
        expect($post->status)->toBe('draft');
        expect($post->views_count)->toBe(0);
        expect($post->published_at)->toBeNull();
    }

    /**
     * 테스트 목적: 포스트-카테고리 관계 검증
     * 테스트 시나리오: 포스트가 카테고리와 올바르게 연결되는지 확인
     * 기대 결과: 포스트의 카테고리 관계가 정상적으로 설정
     * 관련 비즈니스 규칙: 포스트-카테고리 연결 기능
     */
    public function test_포스트_카테고리_관계()
    {
        // Given: 사용자, 카테고리, 포스트 생성
        $user = User::factory()->create();
        $category = Category::create([
            'name' => '기술 블로그',
            'type' => 'post',
        ]);
        $post = Post::create([
            'title' => '테스트 포스트',
            'content' => '내용',
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);

        // When & Then: 관계 확인
        expect($post->category_id)->toBe($category->id);
        expect($post->category->name)->toBe('기술 블로그');
        expect($category->posts)->toHaveCount(1);
        expect($category->posts->first()->title)->toBe('테스트 포스트');
    }

    /**
     * 테스트 목적: 포스트-태그 N:N 관계 검증
     * 테스트 시나리오: 포스트와 태그의 다대다 관계 설정
     * 기대 결과: 포스트에 여러 태그가 연결되고 태그 카운트 업데이트
     * 관련 비즈니스 규칙: 포스트-태그 다대다 관계
     */
    public function test_포스트_태그_관계()
    {
        // Given: 사용자, 태그들, 포스트 생성
        $user = User::factory()->create();
        $tag1 = Tag::create(['name' => 'Laravel']);
        $tag2 = Tag::create(['name' => 'PHP']);
        $post = Post::create([
            'title' => '테스트 포스트',
            'content' => '내용',
            'user_id' => $user->id,
        ]);

        // When: 태그 연결
        $post->tags()->attach([$tag1->id, $tag2->id]);

        // Then: 관계 확인
        expect($post->tags)->toHaveCount(2);
        expect($post->tags->pluck('name')->toArray())->toContain('Laravel');
        expect($post->tags->pluck('name')->toArray())->toContain('PHP');
    }

    /**
     * 테스트 목적: 포스트 발행 상태 스코프 검증
     * 테스트 시나리오: 발행된 포스트만 조회
     * 기대 결과: published 상태이고 published_at이 과거인 포스트만 반환
     * 관련 비즈니스 규칙: 발행된 포스트 필터링
     */
    public function test_발행된_포스트_스코프()
    {
        // Given: 다양한 상태의 포스트 생성
        $user = User::factory()->create();
        $publishedPost = Post::create([
            'title' => '발행된 포스트',
            'content' => '내용',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'user_id' => $user->id,
        ]);
        $draftPost = Post::create([
            'title' => '초안 포스트',
            'content' => '내용',
            'status' => 'draft',
            'user_id' => $user->id,
        ]);
        $futurePost = Post::create([
            'title' => '미래 포스트',
            'content' => '내용',
            'status' => 'published',
            'published_at' => now()->addDay(),
            'user_id' => $user->id,
        ]);

        // When: 발행된 포스트만 조회
        $publishedPosts = Post::published()->get();

        // Then: 발행된 포스트만 반환
        expect($publishedPosts)->toHaveCount(1);
        expect($publishedPosts->first()->title)->toBe('발행된 포스트');
    }

    /**
     * 테스트 목적: 포스트 SEO 메타 관계 검증
     * 테스트 시나리오: 포스트와 SEO 메타 1:1 관계 설정
     * 기대 결과: 포스트에 SEO 메타 정보가 올바르게 연결
     * 관련 비즈니스 규칙: 포스트-SEO 메타 1:1 관계
     */
    public function test_포스트_SEO_메타_관계()
    {
        // Given: 사용자, 포스트, SEO 메타 생성
        $user = User::factory()->create();
        $post = Post::create([
            'title' => '테스트 포스트',
            'content' => '내용',
            'user_id' => $user->id,
        ]);
        $seoMeta = SeoMeta::create([
            'post_id' => $post->id,
            'og_title' => '커스텀 OG 제목',
            'og_description' => 'OG 설명',
        ]);

        // When & Then: 관계 확인
        expect($post->seoMeta)->not->toBeNull();
        expect($post->seoMeta->og_title)->toBe('커스텀 OG 제목');
        expect($seoMeta->post->title)->toBe('테스트 포스트');
    }

    /**
     * 테스트 목적: 포스트 발행 메서드 검증
     * 테스트 시나리오: 초안 포스트를 발행 상태로 변경
     * 기대 결과: 상태가 published로 변경되고 published_at 설정
     * 관련 비즈니스 규칙: 포스트 발행 기능
     */
    public function test_포스트_발행_메서드()
    {
        // Given: 초안 포스트 생성
        $user = User::factory()->create();
        $post = Post::create([
            'title' => '초안 포스트',
            'content' => '내용',
            'status' => 'draft',
            'user_id' => $user->id,
        ]);

        // When: 포스트 발행
        $post->publish();

        // Then: 발행 상태 확인
        expect($post->status)->toBe('published');
        expect($post->published_at)->not->toBeNull();
        expect($post->is_published)->toBeTrue();
    }

    /**
     * 테스트 목적: 포스트 조회수 증가 기능 검증
     * 테스트 시나리오: 포스트 조회 시 views_count 증가
     * 기대 결과: incrementViews 호출 시 조회수가 1 증가
     * 관련 비즈니스 규칙: 포스트 조회수 트래킹
     */
    public function test_포스트_조회수_증가()
    {
        // Given: 포스트 생성
        $user = User::factory()->create();
        $post = Post::create([
            'title' => '테스트 포스트',
            'content' => '내용',
            'user_id' => $user->id,
        ]);

        $initialViews = $post->views_count;

        // When: 조회수 증가
        $post->incrementViews();
        $post->refresh();

        // Then: 조회수가 증가했는지 확인
        expect($post->views_count)->toBe($initialViews + 1);
    }

    /**
     * 테스트 목적: 포스트 요약 접근자 검증
     * 테스트 시나리오: summary가 없을 때 content에서 자동 생성
     * 기대 결과: summary 우선, 없으면 content에서 200자 제한으로 생성
     * 관련 비즈니스 규칙: 포스트 요약 자동 생성
     */
    public function test_포스트_요약_접근자()
    {
        // Given: 다양한 조건의 포스트 생성
        $user = User::factory()->create();
        $postWithSummary = Post::create([
            'title' => '요약 있는 포스트',
            'content' => '긴 내용입니다.',
            'summary' => '커스텀 요약',
            'user_id' => $user->id,
        ]);
        $longContent = str_repeat('This is content. ', 10);
        $postWithoutSummary = Post::create([
            'title' => '요약 없는 포스트',
            'content' => "<p>{$longContent}</p>",
            'user_id' => $user->id,
        ]);

        // When & Then: 요약 접근자 확인
        expect($postWithSummary->excerpt)->toBe('커스텀 요약');
        
        $autoExcerpt = $postWithoutSummary->excerpt;
        expect(strlen($autoExcerpt))->toBeLessThanOrEqual(200);
        expect($autoExcerpt)->toContain('This is content');
    }

    /**
     * 테스트 목적: 포스트 읽기 시간 계산 검증
     * 테스트 시나리오: 포스트 내용에 따른 읽기 시간 계산
     * 기대 결과: 단어 수 기반으로 읽기 시간이 계산됨
     * 관련 비즈니스 규칙: 포스트 읽기 시간 추정
     */
    public function test_포스트_읽기시간_계산()
    {
        // Given: 다양한 길이의 포스트 생성
        $user = User::factory()->create();
        $shortPost = Post::create([
            'title' => '짧은 포스트',
            'content' => 'This is a short post with few words.',
            'user_id' => $user->id,
        ]);
        $longContent = str_repeat('This is a longer post with more words to read. ', 100);
        $longPost = Post::create([
            'title' => '긴 포스트',
            'content' => $longContent,
            'user_id' => $user->id,
        ]);

        // When & Then: 읽기 시간 확인
        expect($shortPost->reading_time)->toBeGreaterThanOrEqual(1);
        expect($longPost->reading_time)->toBeGreaterThan($shortPost->reading_time);
    }
}
