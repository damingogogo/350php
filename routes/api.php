<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 公开接口
Route::get('/home', [App\Http\Controllers\Api\ResourceController::class, 'home']);
Route::get('/categories', [App\Http\Controllers\Api\CategoryController::class, 'index']);
Route::get('/categories/{id}', [App\Http\Controllers\Api\CategoryController::class, 'show']);

// 资源列表和详情（公开）
Route::get('/resources', [App\Http\Controllers\Api\ResourceController::class, 'index']);
Route::get('/resources/{id}', [App\Http\Controllers\Api\ResourceController::class, 'show']);
Route::get('/resources/{id}/comments', [App\Http\Controllers\Api\ResourceController::class, 'comments']);

// 认证接口
Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);

// 需要登录的接口
Route::middleware('auth:sanctum')->group(function () {
    // 用户信息
    Route::get('/user', [App\Http\Controllers\Api\AuthController::class, 'me']);
    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::put('/profile', [App\Http\Controllers\Api\AuthController::class, 'updateProfile']);
    Route::put('/password', [App\Http\Controllers\Api\AuthController::class, 'changePassword']);

    // 用户中心
    Route::get('/my/resources', [App\Http\Controllers\Api\UserController::class, 'myResources']);
    Route::get('/my/favorites', [App\Http\Controllers\Api\UserController::class, 'myFavorites']);
    Route::get('/my/downloads', [App\Http\Controllers\Api\UserController::class, 'myDownloads']);
    Route::get('/my/stats', [App\Http\Controllers\Api\UserController::class, 'stats']);

    // 资源操作
    Route::post('/resources', [App\Http\Controllers\Api\ResourceController::class, 'store']);
    Route::put('/resources/{id}', [App\Http\Controllers\Api\ResourceController::class, 'update']);
    Route::delete('/resources/{id}', [App\Http\Controllers\Api\ResourceController::class, 'destroy']);
    Route::post('/resources/{id}/download', [App\Http\Controllers\Api\ResourceController::class, 'download']);
    Route::post('/resources/{id}/favorite', [App\Http\Controllers\Api\ResourceController::class, 'toggleFavorite']);
    Route::post('/resources/{id}/rate', [App\Http\Controllers\Api\ResourceController::class, 'rate']);
    Route::post('/resources/{id}/comment', [App\Http\Controllers\Api\ResourceController::class, 'storeComment']);

    // 文件上传
    Route::post('/upload/image', [App\Http\Controllers\FileController::class, 'uploadImage']);
    Route::post('/upload/file', [App\Http\Controllers\FileController::class, 'uploadFile']);
});

// 管理后台接口
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // 仪表盘
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'dashboard']);

    // 用户管理
    Route::apiResource('users', App\Http\Controllers\Admin\UserManageController::class);
    Route::put('/users/{id}/role', [App\Http\Controllers\Admin\UserManageController::class, 'changeRole']);

    // 资源管理
    Route::get('/resources', [App\Http\Controllers\Admin\ResourceManageController::class, 'index']);
    Route::put('/resources/{id}/audit', [App\Http\Controllers\Admin\ResourceManageController::class, 'audit']);
    Route::put('/resources/{id}/featured', [App\Http\Controllers\Admin\ResourceManageController::class, 'toggleFeatured']);
    Route::delete('/resources/{id}', [App\Http\Controllers\Admin\ResourceManageController::class, 'destroy']);
    Route::post('/resources/batch-audit', [App\Http\Controllers\Admin\ResourceManageController::class, 'batchAudit']);

    // 分类管理
    Route::apiResource('categories', App\Http\Controllers\Admin\CategoryManageController::class);

    // 评论管理
    Route::get('/comments', [App\Http\Controllers\Admin\CommentManageController::class, 'index']);
    Route::delete('/comments/{id}', [App\Http\Controllers\Admin\CommentManageController::class, 'destroy']);
    Route::post('/comments/batch-delete', [App\Http\Controllers\Admin\CommentManageController::class, 'batchDelete']);
});
