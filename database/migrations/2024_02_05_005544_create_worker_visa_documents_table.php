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
        Schema::create('worker_visa_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('사용자 계정 일련번호');
            $table->unsignedBigInteger('type_id')->comment('문서 유형 일련번호');
            $table->string('title')->comment('문서 제목');
            $table->string('file_path', 512)->comment('문서 저장 경로');
            $table->string('origin_name')->nullable()->comment('원본 파일명');
            $table->timestamps();
            $table->comment('근로자 비자 발급 서류');

            // 제약조건 설정
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('visa_document_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_visa_documents');
    }
};
