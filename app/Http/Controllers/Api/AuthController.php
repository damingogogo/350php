<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    // 用户注册
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => ['required', Rule::in(['student', 'teacher'])],
            'nickname' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'school' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password, // 不加密
            'role' => $request->role,
            'nickname' => $request->nickname ?? $request->username,
            'phone' => $request->phone,
            'school' => $request->school,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => '注册成功',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // 用户登录
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
                    ->orWhere('email', $request->username)
                    ->first();

        if (!$user || $user->password !== $request->password) {
            return response()->json([
                'message' => '用户名或密码错误',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => '登录成功',
            'user' => $user,
            'token' => $token,
        ]);
    }

    // 用户登出
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => '登出成功',
        ]);
    }

    // 获取当前用户信息
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    // 更新用户信息
    public function updateProfile(Request $request)
    {
        $request->validate([
            'nickname' => 'nullable|string|max:50',
            'avatar' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'school' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
        ]);

        $user = $request->user();
        $user->update($request->only([
            'nickname', 'avatar', 'phone', 'school', 'bio'
        ]));

        return response()->json([
            'message' => '更新成功',
            'user' => $user,
        ]);
    }

    // 修改密码
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if ($user->password !== $request->old_password) {
            return response()->json([
                'message' => '原密码错误',
            ], 400);
        }

        $user->update([
            'password' => $request->password,
        ]);

        return response()->json([
            'message' => '密码修改成功',
        ]);
    }
}
