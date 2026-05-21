<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id',
        'user_id',
        'parent_id',
        'title',
        'content',
        'post_type',
        'attachment_path',
        'view_count',
    ];

    protected $casts = [
        'view_count' => 'integer',
    ];

    public function board()
    {
        return $this->belongsTo(ForumBoard::class, 'board_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(ForumPost::class, 'parent_id');
    }
}
