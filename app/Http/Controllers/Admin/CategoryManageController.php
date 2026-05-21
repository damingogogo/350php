<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryManageController extends Controller
{
    // 分类列表
    public function index()
    {
        $categories = Category::withCount('resources')
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->get();

        return response()->json($categories);
    }

    // 创建分类
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'sort' => 'nullable|integer',
        ]);

        $category = Category::create($request->all());

        return response()->json([
            'message' => '创建成功',
            'category' => $category,
        ], 201);
    }

    // 更新分类
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:50',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'sort' => 'nullable|integer',
        ]);

        $category->update($request->all());

        return response()->json([
            'message' => '更新成功',
            'category' => $category,
        ]);
    }

    // 删除分类
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // 检查是否有子分类
        if ($category->children()->count() > 0) {
            return response()->json([
                'message' => '请先删除子分类',
            ], 400);
        }

        // 检查是否有资源
        if ($category->resources()->count() > 0) {
            return response()->json([
                'message' => '该分类下有资源，无法删除',
            ], 400);
        }

        $category->delete();

        return response()->json([
            'message' => '删除成功',
        ]);
    }
}
