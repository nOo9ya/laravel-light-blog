<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 테스트 목적: 포스트 제목으로 검색이 정상 작동하는지 검증
     * 테스트 시나리오: 키워드가 포함된 포스트 제목 검색
     * 기대 결과: 해당 키워드가 포함된 포스트만 반환
     * 관련 비즈니스 규칙: LIKE 검색을 통한 제목 매칭
     */
    public function test_포스트_제목_검색()
    {
        // Given: 포스트들을 생성
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        $post1 = Post::factory()->create([
            'title' => 'Laravel 개발 가이드',
            'content' => 'Laravel로 웹 개발하기',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        $post2 = Post::factory()->create([
            'title' => 'PHP 기초 강좌',
            'content' => 'PHP 프로그래밍 기초',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        $post3 = Post::factory()->create([
            'title' => 'JavaScript 심화',
            'content' => 'Laravel과 함께하는 JavaScript',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        // When: 'Laravel' 키워드로 검색
        $response = $this->get('/search?q=Laravel');

        // Then: Laravel이 포함된 포스트들이 반환됨
        $response->assertStatus(200);
        $response->assertSee('Laravel 개발 가이드');
        $response->assertSee('JavaScript 심화'); // 내용에 Laravel 포함
        $response->assertDontSee('PHP 기초 강좌');
    }

    /**
     * 테스트 목적: 포스트 내용으로 검색이 정상 작동하는지 검증
     * 테스트 시나리오: 키워드가 포함된 포스트 내용 검색
     * 기대 결과: 해당 키워드가 포함된 포스트만 반환
     * 관련 비즈니스 규칙: LIKE 검색을 통한 내용 매칭
     */
    public function test_포스트_내용_검색()
    {
        // Given: 포스트들을 생성
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        $post1 = Post::factory()->create([
            'title' => '웹 개발 기초',
            'content' => 'HTML, CSS, JavaScript를 배워보자',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        $post2 = Post::factory()->create([
            'title' => '백엔드 개발',
            'content' => 'PHP와 Laravel로 서버 개발하기',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        // When: 'JavaScript' 키워드로 검색
        $response = $this->get('/search?q=JavaScript');

        // Then: JavaScript가 포함된 포스트가 반환됨
        $response->assertStatus(200);
        $response->assertSee('웹 개발 기초');
        $response->assertDontSee('백엔드 개발');
    }

    /**
     * 테스트 목적: 태그로 검색이 정상 작동하는지 검증
     * 테스트 시나리오: 특정 태그가 달린 포스트 검색
     * 기대 결과: 해당 태그가 달린 포스트만 반환
     * 관련 비즈니스 규칙: 태그 관계를 통한 검색
     */
    public function test_태그_검색()
    {
        // Given: 태그와 포스트를 생성
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $tag = Tag::factory()->create(['name' => 'laravel']);
        
        $post1 = Post::factory()->create([
            'title' => 'Laravel 튜토리얼',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        $post1->tags()->attach($tag);
        
        $post2 = Post::factory()->create([
            'title' => 'Vue.js 가이드',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        // When: 'laravel' 태그로 검색
        $response = $this->get('/search?q=laravel&type=tag');

        // Then: laravel 태그가 달린 포스트가 반환됨
        $response->assertStatus(200);
        $response->assertSee('Laravel 튜토리얼');
        $response->assertDontSee('Vue.js 가이드');
    }

    /**
     * 테스트 목적: 카테고리로 검색이 정상 작동하는지 검증
     * 테스트 시나리오: 특정 카테고리의 포스트 검색
     * 기대 결과: 해당 카테고리의 포스트만 반환
     * 관련 비즈니스 규칙: 카테고리 관계를 통한 검색
     */
    public function test_카테고리_검색()
    {
        // Given: 카테고리와 포스트를 생성
        $user = User::factory()->create();
        $category1 = Category::factory()->create(['name' => '프론트엔드']);
        $category2 = Category::factory()->create(['name' => '백엔드']);
        
        $post1 = Post::factory()->create([
            'title' => 'React 개발',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category1->id,
        ]);
        
        $post2 = Post::factory()->create([
            'title' => 'Laravel API',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category2->id,
        ]);

        // When: '프론트엔드' 카테고리로 검색
        $response = $this->get('/search?q=프론트엔드&type=category');

        // Then: 프론트엔드 카테고리의 포스트가 반환됨
        $response->assertStatus(200);
        $response->assertSee('React 개발');
        $response->assertDontSee('Laravel API');
    }

    /**
     * 테스트 목적: 페이지 검색이 정상 작동하는지 검증
     * 테스트 시나리오: 키워드가 포함된 페이지 검색
     * 기대 결과: 해당 키워드가 포함된 페이지만 반환
     * 관련 비즈니스 규칙: 페이지 제목/내용 검색
     */
    public function test_페이지_검색()
    {
        // Given: 페이지들을 생성
        $page1 = Page::factory()->create([
            'title' => '회사 소개',
            'content' => '우리 회사는 웹 개발 전문 기업입니다',
            'is_published' => true,
        ]);
        
        $page2 = Page::factory()->create([
            'title' => '서비스 안내',
            'content' => '다양한 서비스를 제공합니다',
            'is_published' => true,
        ]);

        // When: '회사' 키워드로 페이지 검색
        $response = $this->get('/search?q=회사&type=page');

        // Then: 회사가 포함된 페이지가 반환됨
        $response->assertStatus(200);
        $response->assertSee('회사 소개');
        $response->assertDontSee('서비스 안내');
    }

    /**
     * 테스트 목적: 통합 검색이 정상 작동하는지 검증
     * 테스트 시나리오: 모든 타입(포스트, 페이지, 태그, 카테고리)에서 검색
     * 기대 결과: 모든 타입에서 키워드가 포함된 결과 반환
     * 관련 비즈니스 규칙: 통합 검색 기능
     */
    public function test_통합_검색()
    {
        // Given: 다양한 컨텐츠를 생성
        $user = User::factory()->create();
        $category = Category::factory()->create(['name' => '개발']);
        $tag = Tag::factory()->create(['name' => 'development']);
        
        $post = Post::factory()->create([
            'title' => '개발 가이드',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        $page = Page::factory()->create([
            'title' => '개발팀 소개',
            'is_published' => true,
        ]);

        // When: '개발' 키워드로 통합 검색
        $response = $this->get('/search?q=개발');

        // Then: 모든 타입에서 개발이 포함된 결과가 반환됨
        $response->assertStatus(200);
        $response->assertSee('개발 가이드'); // 포스트
        $response->assertSee('개발팀 소개'); // 페이지
        $response->assertSee('개발'); // 카테고리
    }

    /**
     * 테스트 목적: 빈 검색어 처리 검증
     * 테스트 시나리오: 검색어 없이 검색 요청
     * 기대 결과: 적절한 에러 메시지 또는 전체 목록 반환
     * 관련 비즈니스 규칙: 검색어 필수 입력 정책
     */
    public function test_빈_검색어_처리()
    {
        // Given: 검색어 없는 상태

        // When: 빈 검색어로 검색
        $response = $this->get('/search?q=');

        // Then: 적절한 처리 (리다이렉트 또는 에러 메시지)
        $response->assertStatus(302); // 리다이렉트 또는
        // $response->assertStatus(422); // 검증 에러
    }

    /**
     * 테스트 목적: 검색 결과 없음 처리 검증
     * 테스트 시나리오: 매칭되는 결과가 없는 검색
     * 기대 결과: 적절한 "결과 없음" 메시지 표시
     * 관련 비즈니스 규칙: 검색 결과 없음 UI/UX
     */
    public function test_검색_결과_없음_처리()
    {
        // Given: 검색 결과가 없는 상태

        // When: 존재하지 않는 키워드로 검색
        $response = $this->get('/search?q=존재하지않는키워드123');

        // Then: 결과 없음 메시지 표시
        $response->assertStatus(200);
        $response->assertSee('검색 결과가 없습니다');
    }

    /**
     * 테스트 목적: 검색 결과 페이지네이션 검증
     * 테스트 시나리오: 많은 검색 결과가 있는 경우
     * 기대 결과: 페이지네이션이 정상 작동
     * 관련 비즈니스 규칙: 검색 결과 페이징 처리
     */
    public function test_검색_결과_페이지네이션()
    {
        // Given: 많은 포스트를 생성
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        for ($i = 1; $i <= 25; $i++) {
            Post::factory()->create([
                'title' => "테스트 포스트 {$i}",
                'content' => '테스트 내용',
                'status' => 'published',
                'user_id' => $user->id,
                'category_id' => $category->id,
            ]);
        }

        // When: 검색 수행
        $response = $this->get('/search?q=테스트');

        // Then: 페이지네이션 링크가 존재
        $response->assertStatus(200);
        $response->assertSee('테스트 포스트');
        // 페이지네이션 링크 확인 (Laravel 기본 페이지네이션)
        $response->assertSee('Next'); // 또는 적절한 페이지네이션 요소
    }
}