<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * 테스트 목적: 관리자 대시보드 기본 기능 검증
 * 테스트 시나리오: 관리자가 대시보드에서 시스템 상태 확인
 * 기대 결과: 대시보드 통계 정보 정상 표시
 * 관련 비즈니스 규칙: 관리자는 전체 시스템 현황 파악 가능
 */
test('관리자_대시보드_통계_정보_정상_표시', function () {
    // Given: 관리자가 로그인되어 있고
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    // When: 대시보드에 접근하면
    $response = $this->get('/admin/dashboard');

    // Then: 시스템 통계 정보가 표시된다
    $response->assertStatus(200);
    $response->assertSee('전체 포스트');
    $response->assertSee('사용자 수');
    $response->assertSee('카테고리 수');
    $response->assertSee('최근 활동');
});

/**
 * 테스트 목적: 사이트 설정 관리 기능 검증
 * 테스트 시나리오: 관리자가 사이트 이름과 설명 변경
 * 기대 결과: 설정 변경 성공 및 즉시 반영
 * 관련 비즈니스 규칙: 실시간 사이트 설정 변경 가능
 */
test('관리자는_사이트_설정을_변경할_수_있음', function () {
    // Given: 관리자가 로그인되어 있고
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    // When: 사이트 설정을 변경하면
    $response = $this->put('/admin/settings', [
        'site_name' => '새로운 블로그 이름',
        'site_description' => '새로운 블로그 설명',
        'site_theme' => 'modern',
    ]);

    // Then: 설정이 성공적으로 변경된다
    $response->assertRedirect('/admin/settings');
    $response->assertSessionHas('success', '사이트 설정이 업데이트되었습니다.');
    
    // And: 변경된 설정이 저장된다
    $this->assertDatabaseHas('settings', [
        'key' => 'site_name',
        'value' => '새로운 블로그 이름',
    ]);
});

/**
 * 테스트 목적: 테마 변경 기능 검증
 * 테스트 시나리오: 관리자가 웹사이트 테마를 변경
 * 기대 결과: 테마 변경 성공 및 즉시 적용
 * 관련 비즈니스 규칙: 대시보드에서 실시간 테마 전환 가능
 */
test('관리자는_웹사이트_테마를_변경할_수_있음', function () {
    // Given: 관리자가 로그인되어 있고, 현재 테마가 'default'이고
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    // When: 테마를 'modern'으로 변경하면
    $response = $this->put('/admin/theme', [
        'theme' => 'modern',
    ]);

    // Then: 테마가 성공적으로 변경된다
    $response->assertRedirect('/admin/settings');
    $response->assertSessionHas('success', '테마가 변경되었습니다.');
    
    // And: 새 테마 설정이 저장된다
    $this->assertDatabaseHas('settings', [
        'key' => 'site_theme',
        'value' => 'modern',
    ]);
});

/**
 * 테스트 목적: 사용자 관리 기능 접근 검증
 * 테스트 시나리오: 관리자가 사용자 목록 페이지 접근
 * 기대 결과: 사용자 목록 정상 표시
 * 관련 비즈니스 규칙: 관리자만 사용자 관리 권한 보유
 */
test('관리자는_사용자_목록을_볼_수_있음', function () {
    // Given: 여러 사용자가 있고, 관리자가 로그인되어 있고
    $admin = User::factory()->create(['name' => '관리자', 'role' => 'admin']);
    $author = User::factory()->create(['name' => '작성자', 'role' => 'author']);
    
    $this->actingAs($admin);

    // When: 사용자 관리 페이지에 접근하면
    $response = $this->get('/admin/users');

    // Then: 사용자 목록이 표시된다
    $response->assertStatus(200);
    $response->assertSee('관리자');
    $response->assertSee('작성자');
    $response->assertSee('사용자 관리');
});

/**
 * 테스트 목적: 관리자 메뉴 구성 검증
 * 테스트 시나리오: 관리자 대시보드 네비게이션 메뉴 확인
 * 기대 결과: 모든 관리 메뉴 항목 표시
 * 관련 비즈니스 규칙: 관리자는 모든 관리 기능에 접근 가능
 */
test('관리자_대시보드_메뉴_구성_확인', function () {
    // Given: 관리자가 로그인되어 있고
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    // When: 대시보드에 접근하면
    $response = $this->get('/admin/dashboard');

    // Then: 모든 관리 메뉴가 표시된다
    $response->assertSee('대시보드');
    $response->assertSee('포스트 관리');
    $response->assertSee('카테고리 관리');
    $response->assertSee('사용자 관리');
    $response->assertSee('시스템 설정');
    $response->assertSee('테마 관리');
});