<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * 테스트 목적: 관리자 역할 접근 제어 검증
 * 테스트 시나리오: admin 역할 사용자가 관리자 대시보드 접근
 * 기대 결과: 대시보드 정상 접근 가능
 * 관련 비즈니스 규칙: admin 역할만 관리자 기능 접근 가능
 */
test('관리자_역할_사용자는_대시보드_접근_가능', function () {
    // Given: admin 역할을 가진 사용자가 로그인되어 있고
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    // When: 관리자 대시보드에 접근하면
    $response = $this->get('/admin/dashboard');

    // Then: 정상적으로 접근할 수 있다
    $response->assertStatus(200);
    $response->assertSee('관리자 대시보드');
});

/**
 * 테스트 목적: 작성자 역할 접근 제어 검증
 * 테스트 시나리오: author 역할 사용자가 관리자 대시보드 접근 시도
 * 기대 결과: 접근 거부 (403 Forbidden)
 * 관련 비즈니스 규칙: author는 포스트 관리만 가능, 시스템 설정 불가
 */
test('작성자_역할_사용자는_관리자_대시보드_접근_불가', function () {
    // Given: author 역할을 가진 사용자가 로그인되어 있고
    $author = User::factory()->create(['role' => 'author']);
    $this->actingAs($author);

    // When: 관리자 대시보드에 접근을 시도하면
    $response = $this->get('/admin/dashboard');

    // Then: 접근이 거부된다
    $response->assertStatus(403);
});

/**
 * 테스트 목적: 비인증 사용자 접근 제어 검증
 * 테스트 시나리오: 로그인하지 않은 사용자가 대시보드 접근 시도
 * 기대 결과: 로그인 페이지로 리다이렉트
 * 관련 비즈니스 규칙: 인증된 사용자만 관리 영역 접근 가능
 */
test('비인증_사용자는_대시보드_접근시_로그인_페이지로_리다이렉트', function () {
    // Given: 로그인하지 않은 사용자가
    // When: 대시보드에 접근을 시도하면
    $response = $this->get('/admin/dashboard');

    // Then: 로그인 페이지로 리다이렉트된다
    $response->assertRedirect('/login');
});

/**
 * 테스트 목적: 작성자 포스트 관리 권한 검증
 * 테스트 시나리오: author 역할 사용자가 포스트 작성 페이지 접근
 * 기대 결과: 포스트 작성 페이지 정상 접근
 * 관련 비즈니스 규칙: author는 포스트 작성/수정 권한 보유
 */
test('작성자_역할_사용자는_포스트_작성_페이지_접근_가능', function () {
    // Given: author 역할을 가진 사용자가 로그인되어 있고
    $author = User::factory()->create(['role' => 'author']);
    $this->actingAs($author);

    // When: 포스트 작성 페이지에 접근하면
    $response = $this->get('/admin/posts/create');

    // Then: 정상적으로 접근할 수 있다
    $response->assertStatus(200);
});

/**
 * 테스트 목적: 역할별 메뉴 표시 검증
 * 테스트 시나리오: admin과 author 역할에 따른 메뉴 차이 확인
 * 기대 결과: 역할에 맞는 메뉴만 표시
 * 관련 비즈니스 규칙: 사용자 역할에 따른 UI 차별화
 */
test('관리자는_시스템_설정_메뉴가_표시됨', function () {
    // Given: admin 역할을 가진 사용자가 로그인되어 있고
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    // When: 대시보드에 접근하면
    $response = $this->get('/admin/dashboard');

    // Then: 시스템 설정 메뉴가 표시된다
    $response->assertSee('시스템 설정');
    $response->assertSee('테마 관리');
    $response->assertSee('사이트 설정');
});

test('작성자는_포스트_관리_메뉴만_표시됨', function () {
    // Given: author 역할을 가진 사용자가 로그인되어 있고
    $author = User::factory()->create(['role' => 'author']);
    $this->actingAs($author);

    // When: 자신의 대시보드에 접근하면
    $response = $this->get('/author/dashboard');

    // Then: 포스트 관리 메뉴만 표시되고 시스템 설정은 표시되지 않는다
    $response->assertSee('포스트 관리');
    $response->assertDontSee('시스템 설정');
    $response->assertDontSee('테마 관리');
});