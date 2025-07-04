<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ThemeService
{
    /**
     * 현재 활성화된 테마 가져오기
     */
    public function getCurrentTheme(): string
    {
        return Cache::remember('site_theme', 3600, function () {
            $setting = DB::table('settings')->where('key', 'site_theme')->first();
            return $setting ? $setting->value : 'default';
        });
    }

    /**
     * 테마 변경
     */
    public function setTheme(string $theme): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'site_theme'],
            [
                'value' => $theme,
                'type' => 'string',
                'description' => '현재 활성화된 웹사이트 테마',
                'updated_at' => now(),
                'created_at' => now()
            ]
        );

        // 캐시 갱신
        Cache::forget('site_theme');
    }

    /**
     * 사용 가능한 테마 목록
     */
    public function getAvailableThemes(): array
    {
        return [
            'default' => [
                'name' => '기본 테마',
                'description' => '심플하고 깔끔한 기본 테마',
                'preview' => '/images/themes/default-preview.jpg'
            ],
            'modern' => [
                'name' => '모던 테마',
                'description' => '현대적이고 세련된 디자인',
                'preview' => '/images/themes/modern-preview.jpg'
            ],
            'minimal' => [
                'name' => '미니멀 테마',
                'description' => '간결하고 깔끔한 미니멀 디자인',
                'preview' => '/images/themes/minimal-preview.jpg'
            ]
        ];
    }

    /**
     * 테마가 존재하는지 확인
     */
    public function themeExists(string $theme): bool
    {
        return array_key_exists($theme, $this->getAvailableThemes());
    }
}