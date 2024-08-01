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
        Schema::create('visa_passports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visa_application_id')->comment('비자 정보 일련번호');
            $table->unsignedBigInteger('user_id')->comment('회원 마스터 일련번호');
            $table->integer('passport_type')->nullable()->comment('여권 종류');
            $table->string('other_type_detail')->nullable()->comment('기타 여권 상세 내용');
            $table->string('passport_no')->nullable()->comment('여권 번호');
            $table->unsignedBigInteger('passport_country_id')->nullable()->comment('발급 국가');
            $table->string('issue_place')->nullable()->comment('발급지');
            $table->date('issue_date')->nullable()->comment('발급일자');
            $table->date('expire_date')->nullable()->comment('기간 만료일자');
            $table->string('file_path', 512)->nullable()->comment('사본 저장 경로');
            $table->boolean('other_passport')->nullable()->default(0)->comment('다른 여권 소지 여부');
            $table->string('other_passport_detail')->nullable()->comment('다른 여권 상세 내용');
            $table->integer('other_passport_type')->nullable()->comment('여권 종류');
            $table->string('other_passport_no')->nullable()->comment('여권 번호');
            $table->unsignedBigInteger('other_passport_country_id')->nullable()->comment('발급 국가');
            $table->date('other_passport_expire_date')->nullable()->comment('기간 만료일자');
            $table->timestamps();
            $table->comment('여권정보');

            // 제약조건 설정
            $table->foreign('visa_application_id')->references('id')->on('visa_applications')
                ->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('passport_country_id')->references('id')->on('countries')
                ->onDelete('set null');
            $table->foreign('other_passport_country_id')->references('id')->on('countries')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_passports');
    }
};
