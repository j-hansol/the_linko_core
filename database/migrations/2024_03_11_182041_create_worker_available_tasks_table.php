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
        Schema::create('worker_available_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('계정 일련번호');
            $table->string('name')->comment('업무 이름');
            $table->text('description')->nullable()->comment('업무 설명');
            $table->string('file_name')->nullable()->comment('증빙 서류 파일 이름');
            $table->string('file_path', 512)->nullable()->comment('증빙 서류 저장 경로');
            $table->string('movie_name')->nullable()->comment('영상 파일 이름');
            $table->string('movie_path', 512)->nullable()->comment('영상 파일 저장 경로');
            $table->string('movie_link', 2048)->nullable()->comment('영상 파일 저장 외부 URL');
            $table->string('link', 2048)->nullable()->comment('관련 링크');
            $table->timestamps();
            $table->comment('수행 가능 업무');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('available_tasks');
    }
};
