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
        Schema::create('access_tokens', function (Blueprint $table) {
            $table->string('token')->primary()->comment('토큰');
            $table->unsignedBigInteger('user_id')->comment('사용자 Id');
            $table->unsignedBigInteger('switched_user_id')->nullable()->comment('전환계정 일련번호');
            $table->string('uuid', 60)->unique()->comment('UUID (단말기 UUID 사용)');
            $table->boolean('active')->default(0)->comment('정상 토큰 또는 제한 토큰 여부');
            $table->timestamps();
            $table->comment('엑세스 토큰정보');

            // 제약조건 설정
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('switched_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_tokens');
    }
};
