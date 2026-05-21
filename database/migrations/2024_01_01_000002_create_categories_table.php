<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('分类名称');
            $table->string('icon', 255)->nullable()->comment('分类图标');
            $table->string('image', 255)->nullable()->comment('分类封面图片');
            $table->string('description', 255)->nullable()->comment('分类描述');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('父分类ID');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
