<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentManageController extends Controller
{
    // 评论列表
    public function index(Request $request)
    {
        $query = Comment::with(['user', 'resource']);

        if ($request->keyword) {
            $keyword = $request->keyword;
            $query->where('content', 'like', "%{$keyword}%");
        }

        $comments = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($comments);
    }

    // 删除评论
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();

        return response()->json([
            'message' => '删除成功',
        ]);
    }

    // 批量删除
    public function batchDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        Comment::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => '批量删除成功',
        ]);
    }
}
