<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'nickname',
        'email',
        'password',
        'avatar',
        'role',
        'phone',
        'school',
        'department',
        'major',
        'class_name',
        'work_id',
        'student_id',
        'title',
        'bio',
        'points',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'points' => 'integer',
    ];

    // 用户发布的资源
    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    // 用户的评论
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // 用户的收藏
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // 用户的下载记录
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    // 判断是否是管理员
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // 判断是否是教师
    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    // 判断是否是学生
    public function isStudent()
    {
        return $this->role === 'student';
    }
}
