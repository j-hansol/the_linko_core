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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id')->comment('계약정보 일련번호');
            $table->unsignedBigInteger('user_id')->comment('평가 참여 계정 일련번호');
            $table->unsignedBigInteger('target_user_id')->comment('평가 대상 계정 일련번호');
            $table->unsignedBigInteger('assigned_worker_id')->comment('배정 근로자 정보 일련번호');
            $table->unsignedBigInteger('eval_info_id')->comment('평가 정보 일련번호');
            $table->json('answers')->comment('응답 내용');
            $table->double('eval_result')->default(0)->comment('평가 결과');
            $table->timestamps();
            $table->comment('평가정보');

            // 제약 조건 설정
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('target_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_worker_id')->references('id')->on('assigned_workers')->onDelete('cascade');
            $table->foreign('eval_info_id')->references('id')->on('eval_infos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
