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
        Schema::create('worker_experiences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('회원 계정 일련번호');
            $table->unsignedBigInteger('write_user_id')->nullable()->comment('등록자 계정 일련번호');
            $table->string('company_name')->comment('근무 기업명');
            $table->string('company_address')->nullable()->comment('근무 기업 주소');
            $table->string('task')->nullable()->comment('업무');
            $table->string('part')->nullable()->comment('부서');
            $table->string('position')->nullable()->comment('직위/직급');
            $table->date('start_date')->comment('근무 시작일');
            $table->date('end_date')->nullable()->comment('근무 종료일');
            $table->string('file_name')->nullable()->comment('증빙서류 원본 파일명');
            $table->string('file_path', 512)->nullable()->comment('증빙서류 파일 저장 경로');
            $table->timestamps();
            $table->comment('근무 경력');

            // 제약조건 설정
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('write_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('worker_experiences');
    }
};
