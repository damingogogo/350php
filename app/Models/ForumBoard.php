<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumBoard extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'moderator_id',
        'sort',
    ];

    protected $casts = [
        'sort' => 'integer',
    ];

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function posts()
    {
        return $this->hasMany(ForumPost::class, 'board_id');
    }
}
