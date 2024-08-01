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
        Schema::create('contract_worker_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assigned_worker_id')->comment('계약 대상 근로자 정보 일련번호');
            $table->unsignedBigInteger('author_user_id')->comment('등록자 계정 일런번호');
            $table->string('message')->comment('상태관련 메시지');
            $table->timestamps();
            $table->comment('계약 대상 근로자 상태관련 메시지');

            // 제약 조건 설정
            $table->foreign('assigned_worker_id')->references('id')->on('assigned_workers')->onDelete('cascade');
            $table->foreign('author_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_worker_messages');
    }
};
