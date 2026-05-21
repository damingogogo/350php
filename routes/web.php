<?php

use App\Http\Controllers\PlatformController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PlatformController::class, 'home'])->name('platform.home');

Route::get('/login', [PlatformController::class, 'home'])->name('login');
Route::post('/login', [PlatformController::class, 'login'])->name('platform.login');
Route::post('/register', [PlatformController::class, 'register'])->name('platform.register');
Route::post('/logout', [PlatformController::class, 'logout'])->name('platform.logout');
Route::get('/backend/login', [PlatformController::class, 'backendLoginPage'])->name('platform.backend.login');
Route::post('/backend/login', [PlatformController::class, 'backendLogin'])->name('platform.backend.login.submit');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [PlatformController::class, 'dashboard'])->name('platform.dashboard');
    Route::get('/backend', [PlatformController::class, 'roleBackend'])->name('platform.backend');
    Route::get('/student/backend', [PlatformController::class, 'studentBackend'])->name('platform.backend.student');
    Route::get('/teacher/backend', [PlatformController::class, 'teacherBackend'])->name('platform.backend.teacher');
    Route::get('/admin/backend', [PlatformController::class, 'adminBackend'])->name('platform.backend.admin');
    Route::get('/resources', [PlatformController::class, 'resources'])->name('platform.resources');
    Route::get('/resources/{resource}', [PlatformController::class, 'showResource'])->name('platform.resources.show');
    Route::get('/questions', [PlatformController::class, 'questions'])->name('platform.questions');
    Route::get('/questions/{question}', [PlatformController::class, 'showQuestion'])->name('platform.questions.show');
    Route::get('/boards', [PlatformController::class, 'boards'])->name('platform.boards');
    Route::get('/boards/{board}', [PlatformController::class, 'showBoard'])->name('platform.boards.show');
    Route::get('/posts/{post}', [PlatformController::class, 'showPost'])->name('platform.posts.show');
    Route::get('/announcements', [PlatformController::class, 'announcements'])->name('platform.announcements');
    Route::get('/announcements/{announcement}', [PlatformController::class, 'showAnnouncement'])->name('platform.announcements.show');
    Route::post('/profile', [PlatformController::class, 'updateProfile'])->name('platform.profile.update');

    Route::post('/resources', [PlatformController::class, 'storeResource'])->name('platform.resources.store');
    Route::put('/resources/{resource}', [PlatformController::class, 'updateResource'])->name('platform.resources.update');
    Route::post('/resources/{resource}/download', [PlatformController::class, 'downloadResource'])->name('platform.resources.download');
    Route::post('/resources/{resource}/favorite', [PlatformController::class, 'toggleFavorite'])->name('platform.resources.favorite');
    Route::post('/resources/{resource}/comment', [PlatformController::class, 'storeComment'])->name('platform.resources.comment');
    Route::post('/resources/{resource}/rate', [PlatformController::class, 'rateResource'])->name('platform.resources.rate');
    Route::delete('/resources/{resource}', [PlatformController::class, 'deleteResource'])->name('platform.resources.delete');

    Route::post('/announcements', [PlatformController::class, 'storeAnnouncement'])->name('platform.announcements.store');
    Route::put('/announcements/{announcement}', [PlatformController::class, 'updateAnnouncement'])->name('platform.announcements.update');
    Route::delete('/announcements/{announcement}', [PlatformController::class, 'deleteAnnouncement'])->name('platform.announcements.delete');
    Route::post('/questions', [PlatformController::class, 'storeQuestion'])->name('platform.questions.store');
    Route::put('/questions/{question}', [PlatformController::class, 'updateQuestion'])->name('platform.questions.update');
    Route::delete('/questions/{question}', [PlatformController::class, 'deleteQuestion'])->name('platform.questions.delete');
    Route::post('/boards', [PlatformController::class, 'storeBoard'])->name('platform.boards.store');
    Route::put('/boards/{board}', [PlatformController::class, 'updateBoard'])->name('platform.boards.update');
    Route::delete('/boards/{board}', [PlatformController::class, 'deleteBoard'])->name('platform.boards.delete');
    Route::post('/posts', [PlatformController::class, 'storePost'])->name('platform.posts.store');
    Route::put('/posts/{post}', [PlatformController::class, 'updatePost'])->name('platform.posts.update');
    Route::delete('/posts/{post}', [PlatformController::class, 'deletePost'])->name('platform.posts.delete');

    Route::post('/admin/resources/{resource}/status', [PlatformController::class, 'updateResourceStatus'])->name('platform.admin.resources.status');
    Route::post('/admin/users', [PlatformController::class, 'storeUser'])->name('platform.admin.users.store');
    Route::post('/admin/users/{user}', [PlatformController::class, 'updateUser'])->name('platform.admin.users.update');
    Route::delete('/admin/users/{user}', [PlatformController::class, 'deleteUser'])->name('platform.admin.users.delete');
    Route::get('/admin/backup', [PlatformController::class, 'exportBackup'])->name('platform.admin.backup');
});
