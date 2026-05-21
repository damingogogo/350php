<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserManageController extends Controller
{
    // 用户列表
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->keyword) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('username', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%")
                  ->orWhere('nickname', 'like', "%{$keyword}%");
            });
        }

        if ($request->role) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($users);
    }

    // 用户详情
    public function show($id)
    {
        $user = User::with(['resources', 'favorites.resource', 'downloads.resource'])
            ->findOrFail($id);

        return response()->json($user);
    }

    // 创建用户
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['guest', 'student', 'teacher', 'admin'])],
            'nickname' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'school' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
            'nickname' => $request->nickname ?? $request->username,
            'phone' => $request->phone,
            'school' => $request->school,
        ]);

        return response()->json([
            'message' => '创建成功',
            'user' => $user,
        ], 201);
    }

    // 更新用户
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'username' => 'sometimes|string|max:50|unique:users,username,' . $id,
            'email' => 'sometimes|string|email|max:100|unique:users,email,' . $id,
            'role' => ['sometimes', Rule::in(['guest', 'student', 'teacher', 'admin'])],
            'nickname' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'school' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
        ]);

        $data = $request->only(['username', 'email', 'role', 'nickname', 'phone', 'school', 'bio']);

        if ($request->password) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return response()->json([
            'message' => '更新成功',
            'user' => $user,
        ]);
    }

    // 删除用户
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return response()->json([
                'message' => '不能删除最后一个管理员',
            ], 400);
        }

        $user->delete();

        return response()->json([
            'message' => '删除成功',
        ]);
    }

    // 修改用户角色
    public function changeRole(Request $request, $id)
    {
        $request->validate([
            'role' => ['required', Rule::in(['guest', 'student', 'teacher', 'admin'])],
        ]);

        $user = User::findOrFail($id);
        $user->update(['role' => $request->role]);

        return response()->json([
            'message' => '角色修改成功',
            'user' => $user,
        ]);
    }
}
