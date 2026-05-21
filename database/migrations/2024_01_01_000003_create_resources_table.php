<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200)->comment('资源标题');
            $table->text('description')->nullable()->comment('资源描述');
            $table->unsignedBigInteger('user_id')->comment('上传者ID');
            $table->unsignedBigInteger('category_id')->comment('分类ID');
            $table->enum('type', ['document', 'video', 'audio', 'image', 'other'])->default('document')->comment('资源类型');
            $table->string('cover', 255)->nullable()->comment('封面图片');
            $table->string('file_path', 500)->comment('文件路径');
            $table->string('file_name', 200)->comment('文件名');
            $table->bigInteger('file_size')->default(0)->comment('文件大小(字节)');
            $table->string('file_ext', 20)->nullable()->comment('文件扩展名');
            $table->integer('download_count')->default(0)->comment('下载次数');
            $table->integer('view_count')->default(0)->comment('浏览次数');
            $table->decimal('rating', 3, 2)->default(0)->comment('评分');
            $table->integer('rating_count')->default(0)->comment('评分人数');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved')->comment('状态');
            $table->boolean('is_featured')->default(false)->comment('是否推荐');
            $table->string('tags', 500)->nullable()->comment('标签，逗号分隔');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
