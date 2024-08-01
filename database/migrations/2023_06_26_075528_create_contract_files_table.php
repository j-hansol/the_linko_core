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
        Schema::create('contract_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id')->comment('계약정보 일련번호');
            $table->unsignedBigInteger('upload_user_id')->nullable()->comment('등록 회원 계정 일련번호');
            $table->string('title')->comment('제목');
            $table->integer('file_group')->comment('파일 그룹');
            $table->string('origin_name')->comment('원본 파일명');
            $table->string('file_path', 512)->comment('파일 저장 경로');
            $table->timestamps();
            $table->comment('계약관련 파일정보');

            // 제약조건 설정
            $table->foreign('upload_user_id')->references('id')->on('users')
                ->onDelete('set null');
            $table->foreign('contract_id')->references('id')->on('contracts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_files');
    }
};
