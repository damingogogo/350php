<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resource extends Model
{
    use HasFactory, SoftDeletes;

    public const FILE_TYPE_OPTIONS = [
        'word' => 'Word格式',
        'pdf' => 'PDF格式',
        'ppt' => 'PPT课件',
        'excel' => 'Excel表格',
        'audio' => '音频格式',
        'video' => '视频格式',
        'image' => '图片格式',
        'archive' => '压缩包',
        'other' => '其他格式',
    ];

    public const SHARE_SCOPE_OPTIONS = [
        'platform' => '全平台师生共享',
        'teachers' => '教师之间共享',
        'class' => '本班学生共享',
    ];

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'category_id',
        'type',
        'cover',
        'file_path',
        'file_name',
        'file_size',
        'file_ext',
        'file_type',
        'share_scope',
        'class_name',
        'course_name',
        'preview_note',
        'download_count',
        'view_count',
        'rating',
        'rating_count',
        'status',
        'is_featured',
        'tags',
    ];

    protected $casts = [
        'download_count' => 'integer',
        'view_count' => 'integer',
        'rating' => 'decimal:2',
        'rating_count' => 'integer',
        'file_size' => 'integer',
        'is_featured' => 'boolean',
    ];

    protected $appends = [
        'file_type_label',
        'share_scope_label',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    public function getTagsArrayAttribute()
    {
        return $this->tags ? explode(',', $this->tags) : [];
    }

    public function getFileTypeLabelAttribute()
    {
        return self::FILE_TYPE_OPTIONS[$this->file_type ?: self::inferFileType($this->file_ext)] ?? '其他格式';
    }

    public function getShareScopeLabelAttribute()
    {
        return self::SHARE_SCOPE_OPTIONS[$this->share_scope ?: 'platform'] ?? '全平台师生共享';
    }

    public static function inferFileType(?string $extension): string
    {
        $extension = strtolower((string) $extension);

        return match ($extension) {
            'doc', 'docx', 'wps' => 'word',
            'pdf' => 'pdf',
            'ppt', 'pptx' => 'ppt',
            'xls', 'xlsx', 'csv' => 'excel',
            'mp3', 'wav', 'aac', 'flac', 'm4a' => 'audio',
            'mp4', 'mov', 'avi', 'mkv', 'wmv' => 'video',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
            'zip', 'rar', '7z', 'tar', 'gz' => 'archive',
            default => 'other',
        };
    }

    public function isVisibleTo(?User $user): bool
    {
        if ($this->status !== 'approved') {
            return $user && ($user->isAdmin() || $user->id === $this->user_id);
        }

        $scope = $this->share_scope ?: 'platform';

        if ($scope === 'platform') {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($user->isAdmin() || $user->id === $this->user_id) {
            return true;
        }

        if ($scope === 'teachers') {
            return $user->isTeacher();
        }

        if ($scope === 'class') {
            return $user->isStudent() && $this->class_name && $user->class_name === $this->class_name;
        }

        return false;
    }
}
