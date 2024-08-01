<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration  {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('initial_passwords', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->unique()->comment('회원 계정 일련번호');
            $table->unsignedBigInteger('creator_id')->comment('생성자 계정 일련번호');
            $table->string('password', 2048)->comment('초기 비밀번호');
            $table->comment('초기 비밀번호 정보');

            // 제약조건 설정
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('initial_passwords');
    }
};
