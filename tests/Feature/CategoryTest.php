<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 테스트 목적: 카테고리 생성 시 슬러그 자동 생성 검증
     * 테스트 시나리오: 카테고리 생성 시 슬러그가 자동으로 생성되는지 확인
     * 기대 결과: 한글 카테고리명이 영문 슬러그로 변환되어 저장
     * 관련 비즈니스 규칙: 카테고리 슬러그 자동 생성 정책
     */
    public function test_카테고리_생성시_슬러그_자동_생성()
    {
        // Given: 카테고리 데이터 준비
        $categoryData = [
            'name' => '기술 블로그',
            'description' => '기술 관련 포스트 카테고리',
        ];

        // When: 카테고리 생성
        $category = Category::create($categoryData);

        // Then: 슬러그가 자동 생성되었는지 확인
        expect($category->slug)->toBe('기술-블로그');
        expect($category->name)->toBe('기술 블로그');
        expect($category->description)->toBe('기술 관련 포스트 카테고리');
    }

    /**
     * 테스트 목적: 계층형 카테고리 구조 생성 및 관계 검증
     * 테스트 시나리오: 부모-자식 카테고리 관계 설정
     * 기대 결과: 부모 카테고리와 자식 카테고리 관계가 정상적으로 설정
     * 관련 비즈니스 규칙: 계층형 카테고리 구조 지원
     */
    public function test_계층형_카테고리_구조_생성()
    {
        // Given: 부모 카테고리 생성
        $parentCategory = Category::create([
            'name' => '개발',
            'description' => '개발 관련 카테고리',
        ]);

        // When: 자식 카테고리 생성
        $childCategory = Category::create([
            'name' => 'Laravel',
            'description' => 'Laravel 관련 포스트',
            'parent_id' => $parentCategory->id,
        ]);

        // Then: 부모-자식 관계 확인
        expect($childCategory->parent_id)->toBe($parentCategory->id);
        expect($childCategory->parent->name)->toBe('개발');
        expect($parentCategory->children)->toHaveCount(1);
        expect($parentCategory->children->first()->name)->toBe('Laravel');
    }

    /**
     * 테스트 목적: 카테고리 풀네임 접근자 기능 검증
     * 테스트 시나리오: 계층형 카테고리의 전체 경로 표시
     * 기대 결과: 부모 > 자식 형태로 풀네임이 생성
     * 관련 비즈니스 규칙: 카테고리 경로 표시 기능
     */
    public function test_카테고리_풀네임_접근자()
    {
        // Given: 3단계 계층 카테고리 구조 생성
        $grandParent = Category::create(['name' => '기술']);
        $parent = Category::create(['name' => '웹개발', 'parent_id' => $grandParent->id]);
        $child = Category::create(['name' => 'Laravel', 'parent_id' => $parent->id]);

        // When: 풀네임 접근자 호출
        $fullName = $child->full_name;

        // Then: 전체 경로가 올바르게 표시되는지 확인
        expect($fullName)->toBe('기술 > 웹개발 > Laravel');
    }

    /**
     * 테스트 목적: 카테고리 깊이 계산 기능 검증
     * 테스트 시나리오: 카테고리의 계층 깊이 계산
     * 기대 결과: 루트 카테고리는 0, 자식 카테고리는 1, 손자 카테고리는 2
     * 관련 비즈니스 규칙: 카테고리 계층 깊이 계산
     */
    public function test_카테고리_깊이_계산()
    {
        // Given: 다단계 카테고리 구조 생성
        $root = Category::create(['name' => '루트']);
        $child = Category::create(['name' => '자식', 'parent_id' => $root->id]);
        $grandChild = Category::create(['name' => '손자', 'parent_id' => $child->id]);

        // When: 각 카테고리의 깊이 확인
        // Then: 깊이가 올바르게 계산되는지 확인
        expect($root->depth)->toBe(0);
        expect($child->depth)->toBe(1);
        expect($grandChild->depth)->toBe(2);
    }

    /**
     * 테스트 목적: 카테고리 활성 상태 스코프 검증
     * 테스트 시나리오: 활성 카테고리만 조회
     * 기대 결과: is_active가 true인 카테고리만 조회
     * 관련 비즈니스 규칙: 활성 카테고리 필터링
     */
    public function test_활성_카테고리_스코프()
    {
        // Given: 활성/비활성 카테고리 생성
        $activeCategory = Category::create(['name' => '활성', 'is_active' => true]);
        $inactiveCategory = Category::create(['name' => '비활성', 'is_active' => false]);

        // When: 활성 카테고리만 조회
        $activeCategories = Category::active()->get();

        // Then: 활성 카테고리만 반환되는지 확인
        expect($activeCategories)->toHaveCount(1);
        expect($activeCategories->first()->name)->toBe('활성');
    }

    /**
     * 테스트 목적: 포스트용 카테고리 스코프 검증
     * 테스트 시나리오: type이 'post' 또는 'both'인 카테고리만 조회
     * 기대 결과: 포스트에서 사용 가능한 카테고리만 반환
     * 관련 비즈니스 규칙: 카테고리 타입별 필터링 기능
     */
    public function test_포스트용_카테고리_스코프()
    {
        // Given: 다양한 타입의 카테고리 생성
        $postCategory = Category::create(['name' => '포스트용', 'type' => 'post']);
        $pageCategory = Category::create(['name' => '페이지용', 'type' => 'page']);
        $bothCategory = Category::create(['name' => '공용', 'type' => 'both']);

        // When: 포스트용 카테고리만 조회
        $postCategories = Category::forPosts()->get();

        // Then: post와 both 타입만 반환
        expect($postCategories)->toHaveCount(2);
        expect($postCategories->pluck('name')->toArray())->toContain('포스트용');
        expect($postCategories->pluck('name')->toArray())->toContain('공용');
        expect($postCategories->pluck('name')->toArray())->not->toContain('페이지용');
    }

    /**
     * 테스트 목적: 페이지용 카테고리 스코프 검증
     * 테스트 시나리오: type이 'page' 또는 'both'인 카테고리만 조회
     * 기대 결과: 페이지에서 사용 가능한 카테고리만 반환
     * 관련 비즈니스 규칙: 카테고리 타입별 필터링 기능
     */
    public function test_페이지용_카테고리_스코프()
    {
        // Given: 다양한 타입의 카테고리 생성
        $postCategory = Category::create(['name' => '포스트용', 'type' => 'post']);
        $pageCategory = Category::create(['name' => '페이지용', 'type' => 'page']);
        $bothCategory = Category::create(['name' => '공용', 'type' => 'both']);

        // When: 페이지용 카테고리만 조회
        $pageCategories = Category::forPages()->get();

        // Then: page와 both 타입만 반환
        expect($pageCategories)->toHaveCount(2);
        expect($pageCategories->pluck('name')->toArray())->toContain('페이지용');
        expect($pageCategories->pluck('name')->toArray())->toContain('공용');
        expect($pageCategories->pluck('name')->toArray())->not->toContain('포스트용');
    }

    /**
     * 테스트 목적: 카테고리 기본 타입 설정 검증
     * 테스트 시나리오: 카테고리 생성 시 기본 타입이 'both'로 설정
     * 기대 결과: 타입을 지정하지 않으면 'both'로 설정
     * 관련 비즈니스 규칙: 카테고리 기본 타입 정책
     */
    public function test_카테고리_기본_타입_설정()
    {
        // Given: 타입을 지정하지 않은 카테고리 데이터
        $categoryData = [
            'name' => '기본 타입 테스트',
            'description' => '기본 타입 확인용 카테고리',
        ];

        // When: 카테고리 생성
        $category = Category::create($categoryData);

        // Then: 기본 타입이 'both'로 설정되었는지 확인
        expect($category->type)->toBe('both');
    }
}
