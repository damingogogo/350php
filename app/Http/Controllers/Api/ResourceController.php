<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\Category;
use App\Models\Download;
use App\Models\Favorite;
use App\Models\Rating;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ResourceController extends Controller
{
    // 首页数据
    public function home()
    {
        // 最新资源
        $latest = Resource::with(['user', 'category'])
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // 热门下载
        $popular = Resource::with(['user', 'category'])
            ->where('status', 'approved')
            ->orderBy('download_count', 'desc')
            ->take(10)
            ->get();

        // 推荐资源
        $featured = Resource::with(['user', 'category'])
            ->where('status', 'approved')
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        // 分类列表
        $categories = Category::withCount('resources')
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->get();

        return response()->json([
            'latest' => $latest,
            'popular' => $popular,
            'featured' => $featured,
            'categories' => $categories,
        ]);
    }

    // 资源列表（搜索筛选）
    public function index(Request $request)
    {
        $query = Resource::with(['user', 'category'])
            ->where('status', 'approved');

        // 关键词搜索
        if ($request->keyword) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%")
                  ->orWhere('tags', 'like', "%{$keyword}%");
            });
        }

        // 分类筛选
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // 类型筛选
        if ($request->type) {
            $query->where('type', $request->type);
        }

        // 上传者筛选
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // 排序
        $sortField = $request->sort ?? 'created_at';
        $sortOrder = $request->order ?? 'desc';
        $query->orderBy($sortField, $sortOrder);

        $resources = $query->paginate($request->per_page ?? 12);

        return response()->json($resources);
    }

    // 资源详情
    public function show($id)
    {
        $resource = Resource::with(['user', 'category', 'comments.user', 'ratings'])
            ->findOrFail($id);

        // 增加浏览次数
        $resource->increment('view_count');

        // 相关资源推荐
        $related = Resource::with(['user', 'category'])
            ->where('category_id', $resource->category_id)
            ->where('id', '!=', $id)
            ->where('status', 'approved')
            ->take(6)
            ->get();

        // 当前用户的评分
        $userRating = null;
        $isFavorited = false;
        if (auth()->check()) {
            $userRating = Rating::where('user_id', auth()->id())
                ->where('resource_id', $id)
                ->value('score');
            $isFavorited = Favorite::where('user_id', auth()->id())
                ->where('resource_id', $id)
                ->exists();
        }

        return response()->json([
            'resource' => $resource,
            'related' => $related,
            'user_rating' => $userRating,
            'is_favorited' => $isFavorited,
        ]);
    }

    // 下载资源
    public function download($id, Request $request)
    {
        $resource = Resource::findOrFail($id);

        // 游客不能下载
        if (!auth()->check() || auth()->user()->role === 'guest') {
            return response()->json([
                'message' => '请登录后下载',
            ], 401);
        }

        // 记录下载
        Download::create([
            'user_id' => auth()->id(),
            'resource_id' => $id,
            'ip' => $request->ip(),
        ]);

        // 增加下载次数
        $resource->increment('download_count');

        // 返回下载URL
        $downloadUrl = Storage::disk('minio')->temporaryUrl(
            $resource->file_path,
            now()->addHours(1)
        );

        return response()->json([
            'download_url' => $downloadUrl,
            'file_name' => $resource->file_name,
        ]);
    }

    // 上传资源（教师）
    public function store(Request $request)
    {
        // 验证权限
        if (!auth()->check() || !in_array(auth()->user()->role, ['teacher', 'admin'])) {
            return response()->json([
                'message' => '只有教师可以上传资源',
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:document,video,audio,image,other',
            'file' => 'required|file|max:102400', // 最大100MB
            'cover' => 'nullable|image|max:5120', // 最大5MB
            'tags' => 'nullable|string',
        ]);

        // 上传文件到MinIO
        $file = $request->file('file');
        $filePath = $file->store('resources/' . date('Ym'), 'minio');
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $fileExt = $file->getClientOriginalExtension();

        // 上传封面
        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('covers/' . date('Ym'), 'minio');
        }

        $resource = Resource::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => auth()->id(),
            'category_id' => $request->category_id,
            'type' => $request->type,
            'cover' => $coverPath,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'file_ext' => $fileExt,
            'tags' => $request->tags,
            'status' => 'approved', // 直接通过
        ]);

        return response()->json([
            'message' => '上传成功',
            'resource' => $resource,
        ], 201);
    }

    // 更新资源
    public function update(Request $request, $id)
    {
        $resource = Resource::findOrFail($id);

        // 验证权限
        if (auth()->id() !== $resource->user_id && !auth()->user()->isAdmin()) {
            return response()->json([
                'message' => '无权限修改',
            ], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:200',
            'description' => 'nullable|string',
            'category_id' => 'sometimes|exists:categories,id',
            'type' => 'sometimes|in:document,video,audio,image,other',
            'cover' => 'nullable|image|max:5120',
            'tags' => 'nullable|string',
        ]);

        $data = $request->only(['title', 'description', 'category_id', 'type', 'tags']);

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('covers/' . date('Ym'), 'minio');
        }

        $resource->update($data);

        return response()->json([
            'message' => '更新成功',
            'resource' => $resource,
        ]);
    }

    // 删除资源
    public function destroy($id)
    {
        $resource = Resource::findOrFail($id);

        // 验证权限
        if (auth()->id() !== $resource->user_id && !auth()->user()->isAdmin()) {
            return response()->json([
                'message' => '无权限删除',
            ], 403);
        }

        $resource->delete();

        return response()->json([
            'message' => '删除成功',
        ]);
    }

    // 收藏/取消收藏
    public function toggleFavorite($id)
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => '请登录后操作',
            ], 401);
        }

        $resource = Resource::findOrFail($id);

        $favorite = Favorite::where('user_id', auth()->id())
            ->where('resource_id', $id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json([
                'message' => '取消收藏',
                'is_favorited' => false,
            ]);
        }

        Favorite::create([
            'user_id' => auth()->id(),
            'resource_id' => $id,
        ]);

        return response()->json([
            'message' => '收藏成功',
            'is_favorited' => true,
        ]);
    }

    // 评分
    public function rate(Request $request, $id)
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => '请登录后操作',
            ], 401);
        }

        $request->validate([
            'score' => 'required|integer|min:1|max:5',
        ]);

        $resource = Resource::findOrFail($id);

        $rating = Rating::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'resource_id' => $id,
            ],
            [
                'score' => $request->score,
            ]
        );

        // 更新资源平均评分
        $avgRating = Rating::where('resource_id', $id)->avg('score');
        $ratingCount = Rating::where('resource_id', $id)->count();

        $resource->update([
            'rating' => round($avgRating, 2),
            'rating_count' => $ratingCount,
        ]);

        return response()->json([
            'message' => '评分成功',
            'rating' => $resource->rating,
            'rating_count' => $resource->rating_count,
        ]);
    }

    // 评论列表
    public function comments($id)
    {
        $comments = Comment::with(['user', 'replies.user'])
            ->where('resource_id', $id)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($comments);
    }

    // 发表评论
    public function storeComment(Request $request, $id)
    {
        if (!auth()->check() || auth()->user()->role === 'guest') {
            return response()->json([
                'message' => '请登录后评论',
            ], 401);
        }

        $request->validate([
            'content' => 'required|string|max:500',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $resource = Resource::findOrFail($id);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'resource_id' => $id,
            'content' => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        $comment->load('user');

        return response()->json([
            'message' => '评论成功',
            'comment' => $comment,
        ], 201);
    }
}
