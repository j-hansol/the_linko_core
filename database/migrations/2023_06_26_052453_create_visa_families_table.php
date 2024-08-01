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
        Schema::create('visa_families', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visa_application_id')->comment('비자 정보 일련번호');
            $table->unsignedBigInteger('user_id')->comment('회원 마스터 일련번호');
            $table->integer('marital_status')->nullable()->comment('혼인사항');
            $table->string('spouse_family_name')->nullable()->comment('배우자 성');
            $table->string('spouse_given_name')->nullable()->comment('배우자 이름');
            $table->date('spouse_birthday')->nullable()->comment('배우자 생일');
            $table->unsignedBigInteger('spouse_nationality_id')->nullable()->comment('배우자 국적');
            $table->string('spouse_residential_address')->nullable()->comment('배우자 주소');
            $table->string('spouse_contact_no', 40)->nullable()->comment('배우자 전화번호');
            $table->integer('number_of_children')->nullable()->default(0)->comment('자녀 수');
            $table->timestamps();
            $table->comment('가족사항 정보');

            // 조역조건 설정
            $table->foreign('visa_application_id')->references('id')->on('visa_applications')
                ->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('spouse_nationality_id')->references('id')->on('countries')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_families');
    }
};
