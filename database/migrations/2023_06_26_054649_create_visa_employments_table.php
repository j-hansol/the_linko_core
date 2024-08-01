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
        Schema::create('visa_employments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visa_application_id')->comment('비자 정보 일련번호');
            $table->unsignedBigInteger('user_id')->comment('회원 마스터 일련번호');
            $table->integer('job')->nullable()->comment('직업사항');
            $table->string('org_name')->nullable()->comment('회사/기관/학교명');
            $table->string('position_course')->nullable()->comment('직위/과정');
            $table->string('org_address')->nullable()->comment('회사/기관/학교 주소');
            $table->string('org_telephone', 40)->nullable()->comment('전화번호');
            $table->timestamps();
            $table->comment('직업정보');

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
        Schema::dropIfExists('visa_employments');
    }
};
