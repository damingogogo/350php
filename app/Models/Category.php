<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'icon',
        'image',
        'description',
        'parent_id',
        'sort',
    ];

    protected $casts = [
        'sort' => 'integer',
    ];

    // 父分类
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // 子分类
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // 分类下的资源
    public function resources()
    {
        return $this->hasMany(Resource::class);
    }
}
