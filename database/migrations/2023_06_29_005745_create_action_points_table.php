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
        Schema::create('action_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_user_id')->comment('기업 계정 일련번호');
            $table->integer('type')->comment('활동지점 유형');
            $table->string('name')->comment('지점 이름');
            $table->string('address')->comment('주소');
            $table->double('longitude')->comment('경도');
            $table->double('latitude')->comment('위도');
            $table->double('radius')->comment('활동반경');
            $table->timestamps();
            $table->comment('활동지점 정보');

            // 제약조건 설정
            $table->foreign('company_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_points');
    }
};
