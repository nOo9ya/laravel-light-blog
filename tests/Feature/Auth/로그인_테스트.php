<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * 테스트 목적: 사용자 로그인 기능 검증
 * 테스트 시나리오: 유효한 이메일/비밀번호로 로그인 시도
 * 기대 결과: 로그인 성공 후 대시보드로 리다이렉트
 * 관련 비즈니스 규칙: 인증된 사용자만 관리 영역 접근 가능
 */
test('유효한_사용자_정보로_로그인_성공', function () {
    // Given: 사용자가 존재하고
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password123'),
    ]);

    // When: 올바른 이메일과 비밀번호로 로그인을 시도하면
    $response = $this->post('/login', [
        'email' => 'admin@example.com',
        'password' => 'password123',
    ]);

    // Then: 로그인이 성공하고 대시보드로 리다이렉트된다
    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

/**
 * 테스트 목적: 잘못된 로그인 정보 처리 검증
 * 테스트 시나리오: 잘못된 비밀번호로 로그인 시도
 * 기대 결과: 로그인 실패 및 에러 메시지 표시
 * 관련 비즈니스 규칙: 보안을 위한 인증 실패 처리
 */
test('잘못된_비밀번호로_로그인_실패', function () {
    // Given: 사용자가 존재하고
    User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('correct_password'),
    ]);

    // When: 잘못된 비밀번호로 로그인을 시도하면
    $response = $this->post('/login', [
        'email' => 'admin@example.com',
        'password' => 'wrong_password',
    ]);

    // Then: 로그인이 실패하고 에러가 표시된다
    $response->assertStatus(302);
    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

/**
 * 테스트 목적: 로그아웃 기능 검증
 * 테스트 시나리오: 인증된 사용자가 로그아웃 요청
 * 기대 결과: 세션 종료 후 홈페이지로 리다이렉트
 * 관련 비즈니스 규칙: 안전한 세션 종료
 */
test('인증된_사용자_로그아웃_성공', function () {
    // Given: 인증된 사용자가 있고
    $user = User::factory()->create();
    $this->actingAs($user);

    // When: 로그아웃을 요청하면
    $response = $this->post('/logout');

    // Then: 세션이 종료되고 홈페이지로 리다이렉트된다
    $response->assertRedirect('/');
    $this->assertGuest();
});

/**
 * 테스트 목적: 로그인 페이지 접근 검증
 * 테스트 시나리오: 비인증 사용자가 로그인 페이지 접근
 * 기대 결과: 로그인 폼이 정상적으로 표시
 * 관련 비즈니스 규칙: 누구나 로그인 페이지 접근 가능
 */
test('로그인_페이지_정상_표시', function () {
    // Given: 비인증 사용자가
    // When: 로그인 페이지에 접근하면
    $response = $this->get('/login');

    // Then: 로그인 폼이 정상적으로 표시된다
    $response->assertStatus(200);
    $response->assertSee('이메일');
    $response->assertSee('비밀번호');
    $response->assertSee('로그인');
});