<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\ThemeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThemeController extends Controller
{
    protected $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->middleware('admin');
        $this->themeService = $themeService;
    }

    /**
     * 테마 관리 페이지
     */
    public function index(): View
    {
        $themes = $this->themeService->getAvailableThemes();
        $currentTheme = $this->themeService->getCurrentTheme();
        
        return view('admin.themes.index', compact('themes', 'currentTheme'));
    }

    /**
     * 테마 활성화
     */
    public function activate(Request $request): RedirectResponse
    {
        $request->validate([
            'theme' => 'required|string|max:50'
        ], [
            'theme.required' => '테마를 선택해주세요.',
            'theme.max' => '테마 이름이 너무 깁니다.',
        ]);

        $themeName = $request->get('theme');

        // 테마가 실제로 존재하는지 확인
        $availableThemes = $this->themeService->getAvailableThemes();
        if (!array_key_exists($themeName, $availableThemes)) {
            return redirect()->route('admin.themes.index')
                ->with('error', '선택한 테마가 존재하지 않습니다.');
        }

        // 테마 활성화
        $this->themeService->activateTheme($themeName);

        // 설정에 저장
        Setting::updateOrCreate(
            ['key' => 'theme'],
            ['value' => $themeName]
        );

        return redirect()->route('admin.themes.index')
            ->with('success', "'{$availableThemes[$themeName]['name']}' 테마가 활성화되었습니다.");
    }

    /**
     * 테마 미리보기
     */
    public function preview(Request $request): View
    {
        $request->validate([
            'theme' => 'required|string|max:50'
        ]);

        $themeName = $request->get('theme');
        $availableThemes = $this->themeService->getAvailableThemes();

        if (!array_key_exists($themeName, $availableThemes)) {
            abort(404, '테마를 찾을 수 없습니다.');
        }

        // 임시로 테마 변경 (실제 활성화하지는 않음)
        $previewData = [
            'theme' => $availableThemes[$themeName],
            'current_theme' => $this->themeService->getCurrentTheme(),
        ];

        return view('admin.themes.preview', $previewData);
    }

    /**
     * 테마 설정
     */
    public function settings(Request $request): View
    {
        $currentTheme = $this->themeService->getCurrentTheme();
        $themeSettings = $this->themeService->getThemeSettings($currentTheme);

        return view('admin.themes.settings', compact('currentTheme', 'themeSettings'));
    }

    /**
     * 테마 설정 업데이트
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $currentTheme = $this->themeService->getCurrentTheme();
        
        // 테마별 설정 검증 규칙 (기본적인 것들)
        $rules = [
            'primary_color' => 'nullable|string|regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/',
            'secondary_color' => 'nullable|string|regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/',
            'font_family' => 'nullable|string|max:100',
            'font_size' => 'nullable|integer|min:10|max:24',
            'sidebar_position' => 'nullable|string|in:left,right,none',
            'show_breadcrumbs' => 'boolean',
            'show_post_meta' => 'boolean',
            'show_related_posts' => 'boolean',
        ];

        $messages = [
            'primary_color.regex' => '기본 색상은 올바른 HEX 형식이어야 합니다. (예: #3b82f6)',
            'secondary_color.regex' => '보조 색상은 올바른 HEX 형식이어야 합니다. (예: #3b82f6)',
            'font_family.max' => '폰트 이름은 100글자를 초과할 수 없습니다.',
            'font_size.min' => '폰트 크기는 최소 10px 이상이어야 합니다.',
            'font_size.max' => '폰트 크기는 최대 24px까지 가능합니다.',
            'sidebar_position.in' => '사이드바 위치는 왼쪽, 오른쪽, 없음 중 하나여야 합니다.',
        ];

        $validatedData = $request->validate($rules, $messages);

        // 체크박스 필드 처리
        $checkboxFields = ['show_breadcrumbs', 'show_post_meta', 'show_related_posts'];
        foreach ($checkboxFields as $field) {
            $validatedData[$field] = $request->has($field);
        }

        // 테마 설정 저장
        foreach ($validatedData as $key => $value) {
            $settingKey = "theme_{$currentTheme}_{$key}";
            Setting::updateOrCreate(
                ['key' => $settingKey],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : $value]
            );
        }

        return redirect()->route('admin.themes.settings')
            ->with('success', '테마 설정이 성공적으로 저장되었습니다.');
    }

    /**
     * 테마 캐시 클리어
     */
    public function clearCache(): RedirectResponse
    {
        // 뷰 캐시 클리어
        \Artisan::call('view:clear');
        
        // 테마 관련 캐시가 있다면 클리어
        if (method_exists($this->themeService, 'clearCache')) {
            $this->themeService->clearCache();
        }

        return redirect()->route('admin.themes.index')
            ->with('success', '테마 캐시가 클리어되었습니다.');
    }
}