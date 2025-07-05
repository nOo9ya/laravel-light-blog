<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * 설정 페이지 표시
     */
    public function index(): View
    {
        $settings = Setting::all()->pluck('value', 'key');
        
        // 기본값 설정
        $defaultSettings = [
            'site_name' => config('app.name', 'Laravel Light Blog'),
            'site_description' => '가볍고 빠른 Laravel 블로그',
            'site_keywords' => 'Laravel, 블로그, PHP, 웹개발',
            'site_author' => '관리자',
            'theme' => 'default',
            'posts_per_page' => 12,
            'image_quality' => 85,
            'enable_comments' => true,
            'enable_registration' => false,
            'comment_approval' => true,
            'enable_analytics' => true,
            'google_analytics_id' => '',
            'meta_robots' => 'index,follow',
            'timezone' => 'Asia/Seoul',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
        ];

        // 설정값 병합 (저장된 값이 있으면 사용, 없으면 기본값)
        foreach ($defaultSettings as $key => $defaultValue) {
            if (!$settings->has($key)) {
                $settings->put($key, $defaultValue);
            }
        }

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * 설정 업데이트
     */
    public function update(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'site_keywords' => 'nullable|string|max:500',
            'site_author' => 'nullable|string|max:255',
            'theme' => 'required|string|max:50',
            'posts_per_page' => 'required|integer|min:5|max:50',
            'image_quality' => 'required|integer|min:50|max:100',
            'enable_comments' => 'boolean',
            'enable_registration' => 'boolean',
            'comment_approval' => 'boolean',
            'enable_analytics' => 'boolean',
            'google_analytics_id' => 'nullable|string|max:50',
            'meta_robots' => 'required|string|max:100',
            'timezone' => 'required|string|max:50',
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|string|max:20',
        ], [
            'site_name.required' => '사이트 이름을 입력해주세요.',
            'site_name.max' => '사이트 이름은 255글자를 초과할 수 없습니다.',
            'site_description.max' => '사이트 설명은 500글자를 초과할 수 없습니다.',
            'site_keywords.max' => '사이트 키워드는 500글자를 초과할 수 없습니다.',
            'theme.required' => '테마를 선택해주세요.',
            'posts_per_page.required' => '페이지당 포스트 수를 입력해주세요.',
            'posts_per_page.min' => '페이지당 포스트 수는 최소 5개 이상이어야 합니다.',
            'posts_per_page.max' => '페이지당 포스트 수는 최대 50개까지 가능합니다.',
            'image_quality.required' => '이미지 품질을 입력해주세요.',
            'image_quality.min' => '이미지 품질은 최소 50% 이상이어야 합니다.',
            'image_quality.max' => '이미지 품질은 최대 100%까지 가능합니다.',
            'meta_robots.required' => '로봇 메타 태그를 입력해주세요.',
            'timezone.required' => '시간대를 선택해주세요.',
            'date_format.required' => '날짜 형식을 입력해주세요.',
            'time_format.required' => '시간 형식을 입력해주세요.',
        ]);

        // 체크박스 값 처리 (체크되지 않으면 false)
        $checkboxFields = ['enable_comments', 'enable_registration', 'comment_approval', 'enable_analytics'];
        foreach ($checkboxFields as $field) {
            $validatedData[$field] = $request->has($field);
        }

        // 설정 저장
        foreach ($validatedData as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : $value]
            );
        }

        // 캐시 클리어 (config 캐시가 있다면)
        if (function_exists('config_clear')) {
            config_clear();
        }

        return redirect()->route('admin.settings.index')
            ->with('success', '설정이 성공적으로 저장되었습니다.');
    }

    /**
     * 설정 초기화
     */
    public function reset(): RedirectResponse
    {
        Setting::truncate();

        return redirect()->route('admin.settings.index')
            ->with('success', '모든 설정이 초기화되었습니다.');
    }

    /**
     * 캐시 클리어
     */
    public function clearCache(): RedirectResponse
    {
        // Laravel 캐시 클리어
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('view:clear');
        \Artisan::call('route:clear');

        return redirect()->route('admin.settings.index')
            ->with('success', '캐시가 성공적으로 클리어되었습니다.');
    }
}