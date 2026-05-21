<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'resource_id',
        'content',
        'parent_id',
    ];

    // 评论用户
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 评论的资源
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    // 父评论
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // 子评论
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
