<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class ThemeService
{
    protected $themesPath;
    protected $activeTheme;

    public function __construct()
    {
        $this->themesPath = resource_path('views/themes');
        $this->activeTheme = $this->getCurrentTheme();
    }

    /**
     * 현재 활성 테마 조회
     */
    public function getCurrentTheme(): string
    {
        return Setting::where('key', 'theme')->value('value') ?? 'default';
    }

    /**
     * 사용 가능한 테마 목록 조회
     */
    public function getAvailableThemes(): array
    {
        return Cache::remember('available_themes', 3600, function () {
            $themes = [];
            
            if (!File::exists($this->themesPath)) {
                File::makeDirectory($this->themesPath, 0755, true);
            }

            $themeDirs = File::directories($this->themesPath);
            
            foreach ($themeDirs as $themeDir) {
                $themeName = basename($themeDir);
                $configFile = $themeDir . '/theme.json';
                
                if (File::exists($configFile)) {
                    $config = json_decode(File::get($configFile), true);
                    $themes[$themeName] = $config;
                } else {
                    // 기본 테마 정보
                    $themes[$themeName] = [
                        'name' => ucfirst($themeName),
                        'description' => "{$themeName} 테마",
                        'version' => '1.0.0',
                        'author' => 'Unknown',
                        'screenshot' => null,
                    ];
                }
                
                // 스크린샷 확인
                $screenshotPath = "themes/{$themeName}/screenshot.png";
                if (File::exists(public_path($screenshotPath))) {
                    $themes[$themeName]['screenshot'] = asset($screenshotPath);
                }
            }

            // 기본 테마가 없으면 추가
            if (!isset($themes['default'])) {
                $themes['default'] = [
                    'name' => '기본 테마',
                    'description' => 'Laravel Light Blog 기본 테마',
                    'version' => '1.0.0',
                    'author' => 'Laravel Light Blog',
                    'screenshot' => null,
                ];
            }

            return $themes;
        });
    }

    /**
     * 테마 활성화
     */
    public function activateTheme(string $themeName): bool
    {
        $availableThemes = $this->getAvailableThemes();
        
        if (!array_key_exists($themeName, $availableThemes)) {
            return false;
        }

        // 설정에 저장
        Setting::updateOrCreate(
            ['key' => 'theme'],
            ['value' => $themeName]
        );

        $this->activeTheme = $themeName;
        
        // 캐시 클리어
        $this->clearCache();
        
        return true;
    }

    /**
     * 테마 설정 조회
     */
    public function getThemeSettings(string $themeName = null): array
    {
        $themeName = $themeName ?? $this->activeTheme;
        
        return Cache::remember("theme_settings_{$themeName}", 3600, function () use ($themeName) {
            $settings = [];
            
            // 테마별 설정 조회
            $themeSettings = Setting::where('key', 'like', "theme_{$themeName}_%")->get();
            
            foreach ($themeSettings as $setting) {
                $key = str_replace("theme_{$themeName}_", '', $setting->key);
                $settings[$key] = $setting->value;
            }
            
            // 기본값 설정
            $defaultSettings = $this->getDefaultThemeSettings();
            
            return array_merge($defaultSettings, $settings);
        });
    }

    /**
     * 기본 테마 설정값 반환
     */
    protected function getDefaultThemeSettings(): array
    {
        return [
            'primary_color' => '#3b82f6',
            'secondary_color' => '#6b7280',
            'font_family' => 'Inter, system-ui, sans-serif',
            'font_size' => 16,
            'sidebar_position' => 'right',
            'show_breadcrumbs' => true,
            'show_post_meta' => true,
            'show_related_posts' => true,
            'header_style' => 'default',
            'footer_style' => 'default',
            'layout_width' => 'container',
            'enable_dark_mode' => false,
        ];
    }

    /**
     * 테마 설정 저장
     */
    public function saveThemeSettings(string $themeName, array $settings): void
    {
        foreach ($settings as $key => $value) {
            $settingKey = "theme_{$themeName}_{$key}";
            Setting::updateOrCreate(
                ['key' => $settingKey],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : $value]
            );
        }
        
        // 캐시 클리어
        Cache::forget("theme_settings_{$themeName}");
    }

    /**
     * 테마 뷰 파일 경로 반환
     */
    public function getThemeViewPath(string $view, string $themeName = null): string
    {
        $themeName = $themeName ?? $this->activeTheme;
        
        // 테마별 뷰 파일 확인
        $themeView = "themes.{$themeName}.{$view}";
        if (view()->exists($themeView)) {
            return $themeView;
        }
        
        // 기본 테마 뷰 파일 확인
        $defaultView = "themes.default.{$view}";
        if (view()->exists($defaultView)) {
            return $defaultView;
        }
        
        // 일반 뷰 파일 반환
        return $view;
    }

    /**
     * 테마 자산(CSS/JS) 파일 경로 반환
     */
    public function getThemeAssetPath(string $asset, string $themeName = null): string
    {
        $themeName = $themeName ?? $this->activeTheme;
        
        $assetPath = "themes/{$themeName}/{$asset}";
        
        if (File::exists(public_path($assetPath))) {
            return asset($assetPath);
        }
        
        // 기본 테마 자산 확인
        $defaultAssetPath = "themes/default/{$asset}";
        if (File::exists(public_path($defaultAssetPath))) {
            return asset($defaultAssetPath);
        }
        
        return asset($asset);
    }

    /**
     * 테마별 CSS 변수 생성
     */
    public function getThemeCssVariables(string $themeName = null): string
    {
        $settings = $this->getThemeSettings($themeName);
        
        $cssVariables = [
            '--primary-color: ' . $settings['primary_color'],
            '--secondary-color: ' . $settings['secondary_color'],
            '--font-family: ' . $settings['font_family'],
            '--font-size: ' . $settings['font_size'] . 'px',
        ];
        
        return ':root { ' . implode('; ', $cssVariables) . '; }';
    }

    /**
     * 캐시 클리어
     */
    public function clearCache(): void
    {
        Cache::forget('available_themes');
        Cache::forget("theme_settings_{$this->activeTheme}");
        
        // 뷰 캐시 클리어
        if (function_exists('artisan')) {
            \Artisan::call('view:clear');
        }
    }
}