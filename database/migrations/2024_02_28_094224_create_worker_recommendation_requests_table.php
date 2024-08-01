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
        Schema::create('worker_recommendation_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('요청자 계정 일련번호');
            $table->unsignedBigInteger('occupational_group_id')->comment('요청 직업군 일련번호');
            $table->string('title')->comment('제목');
            $table->text('body')->comment('요청 내용');
            $table->integer('worker_count')->nullable()->default(0)->comment('추천 근로자 수');
            $table->timestamps();
            $table->comment('근로자 추천 요청');

            // 제약조건 설정
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('occupational_group_id')->references('id')->on('occupational_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('worker_recommendation_requests');
    }
};
