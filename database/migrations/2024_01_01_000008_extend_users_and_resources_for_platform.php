<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department', 100)->nullable();
            }
            if (!Schema::hasColumn('users', 'major')) {
                $table->string('major', 100)->nullable();
            }
            if (!Schema::hasColumn('users', 'class_name')) {
                $table->string('class_name', 50)->nullable();
            }
            if (!Schema::hasColumn('users', 'work_id')) {
                $table->string('work_id', 50)->nullable();
            }
            if (!Schema::hasColumn('users', 'student_id')) {
                $table->string('student_id', 50)->nullable();
            }
            if (!Schema::hasColumn('users', 'title')) {
                $table->string('title', 50)->nullable();
            }
        });

        Schema::table('resources', function (Blueprint $table) {
            if (!Schema::hasColumn('resources', 'file_type')) {
                $table->string('file_type', 30)->default('other');
            }
            if (!Schema::hasColumn('resources', 'share_scope')) {
                $table->string('share_scope', 30)->default('platform');
            }
            if (!Schema::hasColumn('resources', 'class_name')) {
                $table->string('class_name', 50)->nullable();
            }
            if (!Schema::hasColumn('resources', 'course_name')) {
                $table->string('course_name', 100)->nullable();
            }
            if (!Schema::hasColumn('resources', 'preview_note')) {
                $table->text('preview_note')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            foreach (['file_type', 'share_scope', 'class_name', 'course_name', 'preview_note'] as $column) {
                if (Schema::hasColumn('resources', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            foreach (['department', 'major', 'class_name', 'work_id', 'student_id', 'title'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
