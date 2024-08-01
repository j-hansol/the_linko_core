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
        Schema::create('visa_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visa_application_id')->comment('비자 정보 일련번호');
            $table->unsignedBigInteger('user_id')->comment('회원 마스터 일련번호');
            $table->string('home_address')->nullable()->comment('비자 기준 본국 주소');
            $table->string('current_address')->nullable()->comment('현 거주지 주소');
            $table->string('cell_phone', 40)->nullable()->comment('휴대전화');
            $table->string('emergency_full_name')->nullable()->comment('비상연락 성명');
            $table->unsignedBigInteger('emergency_country_id')->nullable()->comment('비상연락 거주 국가');
            $table->string('emergency_telephone', 40)->nullable()->comment('비상연략 전화번호');
            $table->string('emergency_relationship')->nullable()->comment('비상연락 관계');
            $table->timestamps();
            $table->comment('연락처 정보');

            // 제약조건 설정
            $table->foreign('visa_application_id')->references('id')->on('visa_applications')
                ->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('emergency_country_id')->references('id')->on('countries')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_contacts');
    }
};
