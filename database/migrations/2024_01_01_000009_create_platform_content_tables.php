<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('content')->nullable();
            $table->unsignedBigInteger('publisher_id')->nullable();
            $table->string('publisher_role', 30)->default('admin');
            $table->string('target_role', 30)->default('all');
            $table->string('image', 255)->nullable();
            $table->boolean('is_slider')->default(false);
            $table->integer('sort')->default(0);
            $table->string('status', 30)->default('published');
            $table->timestamps();
            $table->foreign('publisher_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->string('subject_name', 100);
            $table->string('paper_name', 100)->nullable();
            $table->string('question_type', 50)->default('历年真题');
            $table->text('question');
            $table->text('answer')->nullable();
            $table->text('analysis')->nullable();
            $table->string('difficulty', 20)->default('★');
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->timestamps();
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('forum_boards', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->unsignedBigInteger('moderator_id')->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();
            $table->foreign('moderator_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('forum_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('board_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title', 200)->nullable();
            $table->text('content')->nullable();
            $table->string('post_type', 30)->default('normal');
            $table->string('attachment_path', 255)->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->foreign('board_id')->references('id')->on('forum_boards')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('parent_id')->references('id')->on('forum_posts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_posts');
        Schema::dropIfExists('forum_boards');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('announcements');
    }
};
