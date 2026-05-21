<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\Favorite;
use App\Models\Download;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // 我的资源
    public function myResources(Request $request)
    {
        $resources = Resource::with('category')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json($resources);
    }

    // 我的收藏
    public function myFavorites(Request $request)
    {
        $favorites = Favorite::with(['resource.user', 'resource.category'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json($favorites);
    }

    // 我的下载记录
    public function myDownloads(Request $request)
    {
        $downloads = Download::with(['resource.user', 'resource.category'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json($downloads);
    }

    // 用户统计
    public function stats()
    {
        $userId = auth()->id();

        $stats = [
            'resource_count' => Resource::where('user_id', $userId)->count(),
            'favorite_count' => Favorite::where('user_id', $userId)->count(),
            'download_count' => Download::where('user_id', $userId)->count(),
            'total_downloads' => Resource::where('user_id', $userId)->sum('download_count'),
        ];

        return response()->json($stats);
    }
}
