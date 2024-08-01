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
        Schema::create('worker_recommendation_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('worker_recommendation_id')->comment('근로자 추천 마스터 일련번호');
            $table->unsignedBigInteger('user_id')->comment('근로자 계정 일련번호');
            $table->comment('추천된 근로자 장보');

            // 제약조건 설정
            $table->foreign('worker_recommendation_id')->references('id')->on('worker_recommendations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['worker_recommendation_id', 'user_id'], 'recommended_target');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('worker_recommendation_targets');
    }
};
