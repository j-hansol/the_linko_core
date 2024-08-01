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
        Schema::create('worker_education', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('계정 일련번호');
            $table->unsignedBigInteger('write_user_id')->nullable()->comment('등록자 계정 일련번호');
            $table->integer('degree')->comment('학력 구분');
            $table->string('school_name')->comment('학교 이름');
            $table->string('course_name')->nullable()->comment('과정 이름');
            $table->string('start_year')->nullable()->comment('수강 시작 년도');
            $table->string('end_year')->nullable()->comment('수강 종료 년도');
            $table->string('origin_name')->nullable()->comment('원본파일명');
            $table->string('file_path', 512)->nullable()->comment('저장경로');
            $table->timestamps();
            $table->comment('근로자 학력정보');

            // 제약 조건 설정
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('write_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('worker_education');
    }
};
