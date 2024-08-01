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
        Schema::create('order_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_user_id')->comment('요청 사용자 계정 일련번호');
            $table->unsignedBigInteger('target_user_id')->comment('대상 실무자 계정 일련번호');
            $table->integer('task_type')->index()->comment('업무 유형');
            $table->string('model')->nullable()->comment('데이터 모델 클래스명');
            $table->unsignedBigInteger('model_id')->nullable()->comment('데이터 일련번호');
            $table->json('model_data')->nullable()->comment('데이터 원본');
            $table->string('title')->comment('요청 제목');
            $table->string('body')->comment('요청 내용');
            $table->integer('status')->index()->comment('처리 상태');
            $table->timestamps();

            // 제약 조건 설정
            $table->foreign('order_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('target_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('order_tasks');
    }
};
