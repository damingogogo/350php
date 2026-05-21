<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Resource;
use App\Models\Category;
use App\Models\Download;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // 仪表盘统计数据
    public function dashboard()
    {
        // 用户统计
        $userStats = [
            'total' => User::count(),
            'students' => User::where('role', 'student')->count(),
            'teachers' => User::where('role', 'teacher')->count(),
            'guests' => User::where('role', 'guest')->count(),
        ];

        // 资源统计
        $resourceStats = [
            'total' => Resource::count(),
            'approved' => Resource::where('status', 'approved')->count(),
            'pending' => Resource::where('status', 'pending')->count(),
            'rejected' => Resource::where('status', 'rejected')->count(),
            'total_downloads' => Resource::sum('download_count'),
            'total_views' => Resource::sum('view_count'),
        ];

        // 下载统计
        $downloadStats = [
            'total' => Download::count(),
            'today' => Download::whereDate('created_at', today())->count(),
            'this_week' => Download::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Download::whereMonth('created_at', now()->month)->count(),
        ];

        // 分类资源数量
        $categoryStats = Category::withCount('resources')
            ->orderBy('resources_count', 'desc')
            ->take(10)
            ->get();

        // 最近7天下载趋势
        $downloadTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $downloadTrend[] = [
                'date' => $date,
                'count' => Download::whereDate('created_at', $date)->count(),
            ];
        }

        // 最近7天注册趋势
        $registerTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $registerTrend[] = [
                'date' => $date,
                'count' => User::whereDate('created_at', $date)->count(),
            ];
        }

        // 资源类型分布
        $typeDistribution = Resource::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();

        // 热门资源TOP10
        $topResources = Resource::with('user')
            ->orderBy('download_count', 'desc')
            ->take(10)
            ->get();

        // 最新资源
        $latestResources = Resource::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 最新用户
        $latestUsers = User::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'user_stats' => $userStats,
            'resource_stats' => $resourceStats,
            'download_stats' => $downloadStats,
            'category_stats' => $categoryStats,
            'download_trend' => $downloadTrend,
            'register_trend' => $registerTrend,
            'type_distribution' => $typeDistribution,
            'top_resources' => $topResources,
            'latest_resources' => $latestResources,
            'latest_users' => $latestUsers,
        ]);
    }
}
