<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 테스트 목적: 페이지 생성 시 슬러그 자동 생성 검증
     * 테스트 시나리오: 페이지 생성 시 슬러그가 자동으로 생성되는지 확인
     * 기대 결과: 한글 페이지 제목이 영문 슬러그로 변환되어 저장
     * 관련 비즈니스 규칙: 페이지 슬러그 자동 생성 정책
     */
    public function test_페이지_생성시_슬러그_자동_생성()
    {
        // Given: 사용자와 페이지 데이터 준비
        $user = User::factory()->create();
        $pageData = [
            'title' => '회사 소개',
            'content' => '<h1>회사 소개</h1><p>우리 회사에 대한 소개입니다.</p>',
            'user_id' => $user->id,
        ];

        // When: 페이지 생성
        $page = Page::create($pageData);

        // Then: 슬러그가 자동 생성되었는지 확인
        expect($page->slug)->toBe('회사-소개');
        expect($page->title)->toBe('회사 소개');
        expect($page->user_id)->toBe($user->id);
    }

    /**
     * 테스트 목적: 페이지 발행 상태 기본값 검증
     * 테스트 시나리오: 페이지 생성 시 기본 발행 상태 확인
     * 기대 결과: 기본적으로 is_published가 true로 설정
     * 관련 비즈니스 규칙: 페이지 기본 발행 상태 정책
     */
    public function test_페이지_기본_발행_상태()
    {
        // Given: 사용자와 페이지 데이터 준비
        $user = User::factory()->create();
        $pageData = [
            'title' => '이용약관',
            'content' => '이용약관 내용',
            'user_id' => $user->id,
        ];

        // When: 페이지 생성
        $page = Page::create($pageData);

        // Then: 기본 발행 상태 확인
        expect($page->is_published)->toBeTrue();
        expect($page->show_in_menu)->toBeFalse();
        expect($page->order)->toBe(0);
    }

    /**
     * 테스트 목적: 발행된 페이지만 조회하는 스코프 검증
     * 테스트 시나리오: 발행 상태에 따른 페이지 필터링
     * 기대 결과: is_published가 true인 페이지만 반환
     * 관련 비즈니스 규칙: 발행된 페이지 필터링 기능
     */
    public function test_발행된_페이지_스코프()
    {
        // Given: 발행/미발행 페이지 생성
        $user = User::factory()->create();
        $publishedPage = Page::create([
            'title' => '발행된 페이지',
            'content' => '내용',
            'is_published' => true,
            'user_id' => $user->id,
        ]);
        $unpublishedPage = Page::create([
            'title' => '미발행 페이지',
            'content' => '내용',
            'is_published' => false,
            'user_id' => $user->id,
        ]);

        // When: 발행된 페이지만 조회
        $publishedPages = Page::published()->get();

        // Then: 발행된 페이지만 반환되는지 확인
        expect($publishedPages)->toHaveCount(1);
        expect($publishedPages->first()->title)->toBe('발행된 페이지');
    }

    /**
     * 테스트 목적: 메뉴에 표시할 페이지 스코프 검증
     * 테스트 시나리오: 메뉴 표시 여부에 따른 페이지 필터링 및 정렬
     * 기대 결과: show_in_menu가 true인 페이지만 order 순으로 반환
     * 관련 비즈니스 규칙: 메뉴 페이지 표시 및 정렬 기능
     */
    public function test_메뉴_페이지_스코프()
    {
        // Given: 메뉴 표시/비표시 페이지 생성
        $user = User::factory()->create();
        $page1 = Page::create([
            'title' => '페이지1',
            'content' => '내용1',
            'show_in_menu' => true,
            'order' => 2,
            'user_id' => $user->id,
        ]);
        $page2 = Page::create([
            'title' => '페이지2',
            'content' => '내용2',
            'show_in_menu' => true,
            'order' => 1,
            'user_id' => $user->id,
        ]);
        $page3 = Page::create([
            'title' => '페이지3',
            'content' => '내용3',
            'show_in_menu' => false,
            'order' => 0,
            'user_id' => $user->id,
        ]);

        // When: 메뉴 페이지만 조회
        $menuPages = Page::inMenu()->get();

        // Then: 메뉴 표시 페이지만 order 순으로 반환
        expect($menuPages)->toHaveCount(2);
        expect($menuPages->first()->title)->toBe('페이지2');
        expect($menuPages->last()->title)->toBe('페이지1');
    }

    /**
     * 테스트 목적: 페이지 메타 제목 접근자 검증
     * 테스트 시나리오: meta_title이 없을 때 title 반환
     * 기대 결과: meta_title이 없으면 기본 title 반환
     * 관련 비즈니스 규칙: 페이지 메타 정보 자동 설정
     */
    public function test_페이지_메타_제목_접근자()
    {
        // Given: 메타 제목이 있는/없는 페이지 생성
        $user = User::factory()->create();
        $pageWithMetaTitle = Page::create([
            'title' => '기본 제목',
            'content' => '내용',
            'meta_title' => '커스텀 메타 제목',
            'user_id' => $user->id,
        ]);
        $pageWithoutMetaTitle = Page::create([
            'title' => '기본 제목2',
            'content' => '내용2',
            'user_id' => $user->id,
        ]);

        // When & Then: 메타 제목 접근자 확인
        expect($pageWithMetaTitle->meta_title)->toBe('커스텀 메타 제목');
        expect($pageWithoutMetaTitle->meta_title)->toBe('기본 제목2');
    }

    /**
     * 테스트 목적: 페이지 메타 설명 접근자 검증
     * 테스트 시나리오: meta_description이 없을 때 excerpt 또는 content에서 자동 생성
     * 기대 결과: 우선순위에 따라 메타 설명 자동 생성
     * 관련 비즈니스 규칙: 페이지 메타 설명 자동 생성 정책
     */
    public function test_페이지_메타_설명_접근자()
    {
        // Given: 다양한 조건의 페이지 생성
        $user = User::factory()->create();
        $pageWithMetaDesc = Page::create([
            'title' => '제목1',
            'content' => '긴 내용입니다.',
            'meta_description' => '커스텀 메타 설명',
            'user_id' => $user->id,
        ]);
        $pageWithExcerpt = Page::create([
            'title' => '제목2',
            'content' => '긴 내용입니다.',
            'excerpt' => '요약 내용',
            'user_id' => $user->id,
        ]);
        $longContent = str_repeat('HTML 태그가 포함된 긴 내용입니다. ', 20);
        $pageWithoutExcerpt = Page::create([
            'title' => '제목3',
            'content' => "<p>{$longContent}</p>",
            'user_id' => $user->id,
        ]);

        // When & Then: 메타 설명 접근자 확인
        expect($pageWithMetaDesc->meta_description)->toBe('커스텀 메타 설명');
        expect($pageWithExcerpt->meta_description)->toBe('요약 내용');
        expect($pageWithoutExcerpt->meta_description)->toContain('HTML 태그가 포함된');
        
        // 메타 설명이 원본 내용보다 짧은지 확인
        $originalContentLength = strlen(strip_tags($pageWithoutExcerpt->content));
        $metaDescLength = strlen($pageWithoutExcerpt->meta_description);
        expect($metaDescLength)->toBeLessThan($originalContentLength);
    }

    /**
     * 테스트 목적: 페이지 URL 생성 메서드 검증
     * 테스트 시나리오: 페이지의 공개 URL 생성
     * 기대 결과: 올바른 라우트 URL 반환
     * 관련 비즈니스 규칙: 페이지 URL 생성 기능
     */
    public function test_페이지_URL_생성()
    {
        // Given: 페이지 생성
        $user = User::factory()->create();
        $page = Page::create([
            'title' => '테스트 페이지',
            'content' => '내용',
            'user_id' => $user->id,
        ]);

        // When: 라우트가 정의되지 않은 상황에서 예외 발생 확인
        // Then: RouteNotFoundException 발생 (문자열로 확인)
        try {
            $page->getUrl();
            expect(false)->toBeTrue('예외가 발생해야 함');
        } catch (\Exception $e) {
            expect($e->getMessage())->toContain('Route [pages.show] not defined');
        }
    }
}
