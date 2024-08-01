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
        Schema::create('visa_invitors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visa_application_id')->comment('비자 정보 일련번호');
            $table->unsignedBigInteger('user_id')->comment('회원 마스터 일련번호');
            $table->string('invitor')->nullable()->comment('초청 회사/기관/개인');
            $table->string('invitor_relationship')->nullable()->comment('관계');
            $table->date('invitor_birthday')->nullable()->comment('생년월일');
            $table->string('invitor_registration_no')->nullable()->comment('사업자등록번호');
            $table->string('invitor_address')->nullable()->comment('');
            $table->string('invitor_telephone', 40)->nullable()->comment('전화번호');
            $table->string('invitor_cell_phone', 40)->nullable()->comment('휴대전화번호');
            $table->timestamps();
            $table->comment('초청인 정보');

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
        Schema::dropIfExists('visa_invitors');
    }
};
