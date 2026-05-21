<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'resource_id',
        'ip',
    ];

    // 下载用户
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 下载的资源
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}
