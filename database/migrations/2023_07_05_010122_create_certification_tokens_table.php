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
        Schema::create('certification_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('회원 계정 일련번호');
            $table->string('target_function', 60)->comment('인증 필요 기능');
            $table->string('token', 20)->comment('인증 토큰');
            $table->dateTime('expired_at')->comment('만료 일시');
            $table->timestamps();
            $table->comment('인증토큰');

            // 제약조건 설정
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certification_tokens');
    }
};
