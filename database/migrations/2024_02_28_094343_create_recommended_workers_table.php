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
        Schema::create('recommended_workers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('worker_recommendation_id')->comment('근로자 추천 정보 일련번호');
            $table->unsignedBigInteger('worker_user_id')->comment('대상 계정 일련번호');
            $table->comment('추천정보 제공 대상자');

            // 제약조건 설정
            $table->foreign('worker_recommendation_id')->references('id')->on('worker_recommendations')->onDelete('cascade');
            $table->foreign('worker_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['worker_recommendation_id', 'worker_user_id'], 'recommended_worker');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('recommended_workers');
    }
};
