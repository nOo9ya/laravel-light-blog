<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 테스트 목적: 태그 생성 시 슬러그 자동 생성 검증
     * 테스트 시나리오: 태그 생성 시 슬러그가 자동으로 생성되는지 확인
     * 기대 결과: 한글 태그명이 영문 슬러그로 변환되어 저장
     * 관련 비즈니스 규칙: 태그 슬러그 자동 생성 정책
     */
    public function test_태그_생성시_슬러그_자동_생성()
    {
        // Given: 태그 데이터 준비
        $tagData = [
            'name' => '라라벨',
            'description' => 'Laravel 프레임워크 관련 태그',
            'color' => '#ff5722',
        ];

        // When: 태그 생성
        $tag = Tag::create($tagData);

        // Then: 슬러그가 자동 생성되었는지 확인
        expect($tag->slug)->toBe('라라벨');
        expect($tag->name)->toBe('라라벨');
        expect($tag->description)->toBe('Laravel 프레임워크 관련 태그');
        expect($tag->color)->toBe('#ff5722');
    }

    /**
     * 테스트 목적: 태그 기본 색상 설정 검증
     * 테스트 시나리오: 태그 생성 시 기본 색상이 설정되는지 확인
     * 기대 결과: 색상을 지정하지 않으면 기본값 #3b82f6 설정
     * 관련 비즈니스 규칙: 태그 기본 색상 정책
     */
    public function test_태그_기본_색상_설정()
    {
        // Given: 색상 없는 태그 데이터 준비
        $tagData = [
            'name' => 'PHP',
            'description' => 'PHP 언어 관련 태그',
        ];

        // When: 태그 생성
        $tag = Tag::create($tagData);

        // Then: 기본 색상이 설정되었는지 확인
        expect($tag->color)->toBe('#3b82f6');
    }

    /**
     * 테스트 목적: 태그 포스트 카운트 업데이트 기능 검증
     * 테스트 시나리오: 태그에 포스트가 연결될 때 카운트 업데이트
     * 기대 결과: 태그의 post_count가 연결된 포스트 수와 일치
     * 관련 비즈니스 규칙: 태그별 포스트 카운트 관리
     */
    public function test_태그_포스트_카운트_업데이트()
    {
        // Given: 태그와 사용자, 포스트 생성
        $tag = Tag::create(['name' => 'Laravel']);
        $user = User::factory()->create();
        $post1 = Post::create([
            'title' => '테스트 포스트 1',
            'content' => '내용 1',
            'user_id' => $user->id,
        ]);
        $post2 = Post::create([
            'title' => '테스트 포스트 2',
            'content' => '내용 2',
            'user_id' => $user->id,
        ]);

        // When: 태그에 포스트 연결
        $tag->posts()->attach([$post1->id, $post2->id]);

        // Then: 태그 포스트 카운트 업데이트
        $tag->updatePostCount();
        $tag->refresh();

        expect($tag->post_count)->toBe(2);
    }

    /**
     * 테스트 목적: 인기 태그 스코프 검증
     * 테스트 시나리오: 포스트 수에 따른 인기 태그 정렬
     * 기대 결과: post_count가 높은 순으로 정렬된 태그 반환
     * 관련 비즈니스 규칙: 인기 태그 정렬 기능
     */
    public function test_인기_태그_스코프()
    {
        // Given: 포스트 카운트가 다른 태그들 생성
        $tag1 = Tag::create(['name' => '태그1', 'post_count' => 5]);
        $tag2 = Tag::create(['name' => '태그2', 'post_count' => 10]);
        $tag3 = Tag::create(['name' => '태그3', 'post_count' => 3]);

        // When: 인기 태그 조회
        $popularTags = Tag::popular(2)->get();

        // Then: 포스트 수가 높은 순으로 정렬되어 반환
        expect($popularTags)->toHaveCount(2);
        expect($popularTags->first()->name)->toBe('태그2');
        expect($popularTags->last()->name)->toBe('태그1');
    }

    /**
     * 테스트 목적: 포스트가 있는 태그만 조회하는 스코프 검증
     * 테스트 시나리오: post_count가 0보다 큰 태그만 조회
     * 기대 결과: 포스트가 연결된 태그만 반환
     * 관련 비즈니스 규칙: 사용 중인 태그 필터링
     */
    public function test_포스트가_있는_태그_스코프()
    {
        // Given: 포스트 카운트가 0과 0보다 큰 태그들 생성
        $tagWithPosts = Tag::create(['name' => '사용중', 'post_count' => 5]);
        $tagWithoutPosts = Tag::create(['name' => '미사용', 'post_count' => 0]);

        // When: 포스트가 있는 태그만 조회
        $tagsWithPosts = Tag::withPosts()->get();

        // Then: 포스트가 있는 태그만 반환
        expect($tagsWithPosts)->toHaveCount(1);
        expect($tagsWithPosts->first()->name)->toBe('사용중');
    }

    /**
     * 테스트 목적: 태그 클라우드 기능 검증
     * 테스트 시나리오: 포스트가 있는 태그들을 인기순으로 조회
     * 기대 결과: 포스트 수가 많은 순으로 정렬된 태그 목록 반환
     * 관련 비즈니스 규칙: 태그 클라우드 표시 기능
     */
    public function test_태그_클라우드_기능()
    {
        // Given: 다양한 포스트 카운트를 가진 태그들 생성
        $tag1 = Tag::create(['name' => '태그1', 'post_count' => 0]);
        $tag2 = Tag::create(['name' => '태그2', 'post_count' => 8]);
        $tag3 = Tag::create(['name' => '태그3', 'post_count' => 12]);
        $tag4 = Tag::create(['name' => '태그4', 'post_count' => 5]);

        // When: 태그 클라우드 조회
        $tagCloud = Tag::getTagCloud(3);

        // Then: 포스트가 있는 태그만 인기순으로 반환
        expect($tagCloud)->toHaveCount(3);
        expect($tagCloud->first()->name)->toBe('태그3');
        expect($tagCloud->get(1)->name)->toBe('태그2');
        expect($tagCloud->last()->name)->toBe('태그4');
    }
}
