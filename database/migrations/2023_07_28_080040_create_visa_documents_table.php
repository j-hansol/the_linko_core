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
        Schema::create('visa_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visa_application_id')->comment('비자정보 일련번호');
            $table->unsignedBigInteger('user_id')->comment('사용자 계정 일련번호');
            $table->unsignedBigInteger('type_id')->comment('문서 유형 일련번호');
            $table->string('title')->comment('문서 제목');
            $table->string('file_path', 512)->comment('문서 저장 경로');
            $table->timestamps();

            // 제약조건 설정
            $table->foreign('visa_application_id')->references('id')->on('visa_applications')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('visa_document_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('visa_documents');
    }
};
