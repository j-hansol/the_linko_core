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
        Schema::create('visa_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visa_application_id')->comment('비자 정보 일련번호');
            $table->unsignedBigInteger('user_id')->comment('회원 마스터 일련번호');
            $table->double('travel_costs')->nullable()->default(0)->comment('방문경비');
            $table->string('payer_name')->nullable()->comment('경비 지불 기관');
            $table->string('payer_relationship')->nullable()->comment('기관과의 관계');
            $table->string('support_type')->nullable()->comment('지원 내용');
            $table->string('payer_contact', 40)->nullable()->comment('기관 연락처');
            $table->timestamps();
            $table->comment('방문경비 정보');

            // 제약조건 설정
            $table->foreign('visa_application_id')->references('id')->on('visa_applications')
                ->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_costs');
    }
};
