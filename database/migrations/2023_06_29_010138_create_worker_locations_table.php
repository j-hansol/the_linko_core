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
        Schema::create('worker_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('worker_user_id')->comment('사용자(인력) 계정 일련번호');
            $table->double('longitude')->comment('경도');
            $table->double('latitude')->comment('위도');
            $table->timestamp('device_time')->comment('단말기 기준 날ㅉ/시간');
            $table->timestamps();
            $table->foreign('worker_user_id')->references('id')->on('users')->onDelete('cascade');

            // 제약조건 설정
            $table->comment('인력 위치정보');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_locations');
    }
};
