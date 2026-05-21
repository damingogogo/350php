<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // 分类列表
    public function index()
    {
        $categories = Category::with('children')
            ->withCount('resources')
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->get();

        return response()->json($categories);
    }

    // 分类详情
    public function show($id)
    {
        $category = Category::with('children')
            ->withCount('resources')
            ->findOrFail($id);

        return response()->json($category);
    }
}
