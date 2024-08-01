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
        Schema::create('visa_vist_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visa_application_id')->comment('비자 정보 일련번호');
            $table->unsignedBigInteger('user_id')->comment('회원 마스터 일련번호');
            $table->integer('purpose')->nullable()->comment('방문목적');
            $table->string('other_purpose_detail')->nullable()->comment('기타 인경우 상세 내용');
            $table->integer('intended_stay_period')->nullable()->comment('기타 인경우 상세 내용');
            $table->date('intended_entry_date')->nullable()->comment('intended_entry_date');
            $table->string('address_in_korea')->nullable()->comment('체류예정지 주소');
            $table->string('contact_in_korea', 40)->nullable()->comment('국내 연락처');
            $table->json('visit_korea_ids')->nullable()->comment('한국 방문 이력정보');
            $table->json('visit_country_ids')->nullable()->comment('한국 외 다른 국가 방문 내역');
            $table->json('stay_family_ids')->nullable()->comment('국내 거주 가족 내역');
            $table->json('family_member_ids')->nullable()->comment('동반 입국 가족 내역');
            $table->timestamps();
            $table->comment('방문상셀 정보');

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
        Schema::dropIfExists('visa_vist_details');
    }
};
