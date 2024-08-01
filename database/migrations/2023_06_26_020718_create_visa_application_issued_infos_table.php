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
        Schema::create('visa_application_issued_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visa_application_id')->comment('비자증보 (마스터)일련번호');
            $table->unsignedBigInteger('user_id')->comment('회원 마스터 일련번호');
            $table->unsignedBigInteger('attorney_user_id')->nullable()
                ->comment('비자 신청 업무 담당 행정사 회원 일련번호');
            $table->string('application_no')->comment('사증 번호');
            $table->integer('application_type')->comment('사증 종류');
            $table->string('stay_status')->comment('체류자격');
            $table->integer('stay_period')->default(0)->comment('채류기간');
            $table->date('issue_date')->comment('발급일');
            $table->string('issue_Institution')->comment('발급기관');
            $table->date('validity_period')->comment('유효기간(만료일)');
            $table->timestamps();
            $table->comment('바자 발급 정보');

            // 제약조건 설정
            $table->foreign('visa_application_id')->references('id')->on('visa_applications')
                ->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('attorney_user_id')->references('id')->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_application_issued_infos');
    }
};
