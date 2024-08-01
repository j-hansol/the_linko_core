<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_user_id')->comment('회원 일련번호');
            $table->string('name')->comment('업무 이름');
            $table->string('en_name')->comment('업무 이름 (영문)');
            $table->string('description')->nullable()->comment('업무 설명');
            $table->string('en_description')->nullable()->comment('업무 설명 (영문)');
            $table->string('movie_file_path', 512)->nullable()->comment('동영상 파일 경로');
            $table->timestamps();
            $table->comment('업무정보');

            // 제약조건 설정
            $table->foreign('company_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_languages');
        Schema::dropIfExists('tasks');
    }
};
