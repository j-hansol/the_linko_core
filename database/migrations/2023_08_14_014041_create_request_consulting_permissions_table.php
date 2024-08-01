<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration  {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('request_consulting_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visa_application_id')->comment('비자 정보 일련번호');
            $table->unsignedBigInteger('user_id')->comment('회원 마스터 일련번호');
            $table->dateTime('max_datetime')->comment('최대 처리 기한');
            $table->integer('status')->comment('처리 상태')->index();
            $table->timestamps();
            $table->comment('컨설팅 권한 요청');

            // 제약조건 설정
            $table->foreign('visa_application_id')->references('id')->on('visa_applications')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('request_consulting_permissions');
    }
};
