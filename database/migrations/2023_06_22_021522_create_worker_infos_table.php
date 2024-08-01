<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('worker_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('회원 일련번호');
            $table->string('skills')->nullable()->comment('보유 기술');
            $table->string('jobs')->nullable()->comment('회망 직업군');
            $table->string('hobby')->nullable()->comment('취미');
            $table->string('education_part')->nullable()->comment('희망 교육 분야');
            $table->boolean('medical_support')->nullable()->default(0)->comment('의료지원');
            $table->float('height')->nullable()->default(0)->comment('키');
            $table->float('weight')->nullable()->default(0)->comment('몸무게');
            $table->string('blood_type')->nullable()->comment('혈액형');
            $table->timestamps();
            $table->comment('근로자 추가정보');

            // 제약조건 설정
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('worker_infos');
    }
};
