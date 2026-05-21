<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'publisher_id',
        'publisher_role',
        'target_role',
        'image',
        'is_slider',
        'sort',
        'status',
    ];

    protected $casts = [
        'is_slider' => 'boolean',
        'sort' => 'integer',
    ];

    public function publisher()
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }
}
