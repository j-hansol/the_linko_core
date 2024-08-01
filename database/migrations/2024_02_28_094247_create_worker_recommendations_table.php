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
        Schema::create('worker_recommendations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('worker_recommendation_request_id')->comment('요청정보 일련번호');
            $table->unsignedBigInteger('user_id')->comment('추천자 계정 일련번호');
            $table->json('provided_models')->comment('제공 대상 정보');
            $table->json('excluded_informations')->nullable()->comment('제외 대상 정보');
            $table->date('expire_date')->comment('제공 만료일');
            $table->timestamps();
            $table->comment('근로자 추천');

            // 제약조건 설정
            $table->foreign('worker_recommendation_request_id')->references('id')->on('worker_recommendation_requests')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('worker_recommendations');
    }
};
