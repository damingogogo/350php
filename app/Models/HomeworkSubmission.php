<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeworkSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_name',
        'assignment_title',
        'content',
        'attachment_path',
        'attachment_name',
        'status',
        'teacher_comment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
