<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique()->comment('用户名');
            $table->string('nickname', 50)->nullable()->comment('昵称');
            $table->string('email', 100)->unique()->comment('邮箱');
            $table->string('password', 255)->comment('密码');
            $table->string('avatar', 255)->nullable()->comment('头像');
            $table->enum('role', ['guest', 'student', 'teacher', 'admin'])->default('student')->comment('角色');
            $table->string('phone', 20)->nullable()->comment('手机号');
            $table->string('school', 100)->nullable()->comment('学校');
            $table->text('bio')->nullable()->comment('个人简介');
            $table->integer('points')->default(0)->comment('积分');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
