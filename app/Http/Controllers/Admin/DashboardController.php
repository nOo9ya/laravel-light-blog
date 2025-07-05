<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Category;
use App\Services\AnalyticsService;
use App\Services\ErrorLogService;
use App\Services\ErrorNotificationService;
use App\Models\NotificationSetting;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    /**
     * 관리자 대시보드 표시
     */
    public function index()
    {
        // 기본 통계 데이터
        $stats = [
            'total_posts' => Post::count(),
            'total_users' => User::count(),
            'total_categories' => Category::count(),
            'pending_comments' => Comment::pending()->count(),
            'total_comments' => Comment::count(),
            'spam_comments' => Comment::spam()->count(),
            'recent_activity' => '최근 활동 없음' // 추후 구현
        ];

        return view(themed('admin.dashboard'), compact('stats'));
    }

    /**
     * 분석 통계 페이지
     */
    public function analytics(AnalyticsService $analyticsService)
    {
        $stats = $analyticsService->getDashboardStats();
        
        return view(themed('admin.analytics.index'), compact('stats'));
    }

    /**
     * 분석 데이터 내보내기
     */
    public function exportAnalytics(AnalyticsService $analyticsService)
    {
        $stats = $analyticsService->getDashboardStats();
        
        return response()->json([
            'success' => true,
            'data' => $stats,
            'exported_at' => now()->toISOString(),
        ]);
    }

    /**
     * 실시간 분석 데이터
     */
    public function realTimeAnalytics(AnalyticsService $analyticsService)
    {
        $activity = $analyticsService->getRealTimeActivity();
        
        return response()->json([
            'success' => true,
            'data' => $activity,
        ]);
    }

    /**
     * 에러 로그 목록 조회
     */
    public function errorLogs(ErrorLogService $errorLogService)
    {
        $logs = $errorLogService->getRecentErrorLogs(100);
        $stats = $errorLogService->getErrorStatistics();
        $fileInfo = $errorLogService->getLogFileInfo();
        
        return view(themed('admin.logs.index'), compact('logs', 'stats', 'fileInfo'));
    }

    /**
     * 에러 로그 AJAX 조회
     */
    public function errorLogsAjax(Request $request, ErrorLogService $errorLogService)
    {
        $limit = $request->input('limit', 50);
        $search = $request->input('search', '');
        
        if (!empty($search)) {
            $logs = $errorLogService->searchErrors($search, $limit);
        } else {
            $logs = $errorLogService->getRecentErrorLogs($limit);
        }
        
        return response()->json([
            'success' => true,
            'logs' => $logs,
            'stats' => $errorLogService->getErrorStatistics()
        ]);
    }

    /**
     * 에러 로그 삭제
     */
    public function clearErrorLogs(ErrorLogService $errorLogService)
    {
        $success = $errorLogService->clearErrorLogs();
        
        if ($success) {
            return back()->with('success', '에러 로그가 성공적으로 삭제되었습니다.');
        } else {
            return back()->with('error', '에러 로그 삭제에 실패했습니다.');
        }
    }

    /**
     * 알림 설정 페이지
     */
    public function notificationSettings()
    {
        $settings = NotificationSetting::getSettings();
        $services = $settings->getEnabledServices();
        
        return view(themed('admin.settings.notifications'), compact('settings', 'services'));
    }

    /**
     * 알림 설정 저장
     */
    public function updateNotificationSettings(Request $request)
    {
        $settings = NotificationSetting::getSettings();
        
        // 유효성 검사
        $request->validate([
            'slack_enabled' => 'boolean',
            'discord_enabled' => 'boolean',
            'telegram_enabled' => 'boolean',
            'slack_webhook_url' => 'nullable|url',
            'discord_webhook_url' => 'nullable|url',
            'telegram_bot_token' => 'nullable|string',
            'telegram_chat_id' => 'nullable|string',
            'notification_levels' => 'array',
            'throttle_minutes' => 'integer|min:1|max:60'
        ]);
        
        try {
            $updateData = [
                'slack_enabled' => $request->boolean('slack_enabled'),
                'discord_enabled' => $request->boolean('discord_enabled'),
                'telegram_enabled' => $request->boolean('telegram_enabled'),
                'slack_webhook_url' => $request->input('slack_webhook_url'),
                'slack_channel' => $request->input('slack_channel'),
                'slack_username' => $request->input('slack_username', 'Laravel Error Bot'),
                'discord_webhook_url' => $request->input('discord_webhook_url'),
                'discord_username' => $request->input('discord_username', 'Laravel Error Bot'),
                'telegram_bot_token' => $request->input('telegram_bot_token'),
                'telegram_chat_id' => $request->input('telegram_chat_id'),
                'notification_levels' => $request->input('notification_levels', []),
                'throttle_minutes' => $request->input('throttle_minutes', 5),
                'test_mode' => $request->boolean('test_mode')
            ];
            
            $settings->update($updateData);
            
            return back()->with('success', '알림 설정이 성공적으로 저장되었습니다.');
        } catch (\Exception $e) {
            return back()->with('error', '설정 저장 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 테스트 알림 전송
     */
    public function sendTestNotification(ErrorNotificationService $notificationService)
    {
        // 설정 새로고침
        $notificationService->refreshSettings();
        
        $results = $notificationService->sendTestNotification();
        
        return response()->json([
            'success' => true,
            'results' => $results,
            'message' => '테스트 알림이 전송되었습니다.'
        ]);
    }
}
