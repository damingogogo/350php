<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceManageController extends Controller
{
    // 资源列表
    public function index(Request $request)
    {
        $query = Resource::with(['user', 'category']);

        if ($request->keyword) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $resources = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($resources);
    }

    // 审核资源
    public function audit(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'reason' => 'nullable|string|max:500',
        ]);

        $resource = Resource::findOrFail($id);
        $resource->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => $request->status === 'approved' ? '审核通过' : '审核拒绝',
            'resource' => $resource,
        ]);
    }

    // 设置/取消推荐
    public function toggleFeatured($id)
    {
        $resource = Resource::findOrFail($id);
        $resource->update([
            'is_featured' => !$resource->is_featured,
        ]);

        return response()->json([
            'message' => $resource->is_featured ? '已设为推荐' : '已取消推荐',
            'resource' => $resource,
        ]);
    }

    // 删除资源
    public function destroy($id)
    {
        $resource = Resource::findOrFail($id);
        $resource->delete();

        return response()->json([
            'message' => '删除成功',
        ]);
    }

    // 批量审核
    public function batchAudit(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'status' => 'required|in:approved,rejected',
        ]);

        Resource::whereIn('id', $request->ids)->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => '批量审核成功',
        ]);
    }
}
