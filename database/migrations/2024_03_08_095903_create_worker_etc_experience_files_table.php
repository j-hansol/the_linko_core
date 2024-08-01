<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('worker_etc_experience_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('회원 계정 일련번호');
            $table->unsignedBigInteger('write_user_id')->nullable()->comment('등록자 계정 일련번호');
            $table->string('file_name')->nullable()->comment('증빙서류 원본 파일명');
            $table->string('file_path', 512)->nullable()->comment('증빙서류 파일 저장 경로');
            $table->timestamps();
            $table->comment('기타 경력 정빙 서류');

            // 제약조건 설정
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('write_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('worker_etc_experience_files');

    }
};
