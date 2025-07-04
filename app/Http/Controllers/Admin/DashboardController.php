<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Category;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

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
}
