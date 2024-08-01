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
        Schema::create('task_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_task_id')->comment('요청 업무 일련번호');
            $table->unsignedBigInteger('user_id')->comment('등록한 사용자 계정 일련번호');
            $table->string('title')->comment('업무 보고 제목');
            $table->text('body')->comment('보고 내용');
            $table->timestamps();

            // 제약조건 설정
            $table->foreign('order_task_id')->references('id')->on('order_tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('task_reports');
    }
};
