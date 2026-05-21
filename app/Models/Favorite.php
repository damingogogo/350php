<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'resource_id',
    ];

    // 收藏用户
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 收藏的资源
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}
