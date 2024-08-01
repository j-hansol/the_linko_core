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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->integer('device_type')->default(\App\Lib\DeviceType::TYPE_MOBILE->value)->comment('단말기 종류(고정형, 모바일)');
            $table->unsignedBigInteger('user_id')->comment('사용자 일련번호');
            $table->string('name')->nullable()->comment('단말기 이름');
            $table->string('uuid', 60)->unique()->comment('단말기 ID(UUID)');
            $table->string('fcm_token')->nullable()->comment('FCM 토큰');
            $table->timestamps();
            $table->comment('접속 단말기 정보');

            // 제약조건 설정
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'device_type'], 'user_device_type' );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
