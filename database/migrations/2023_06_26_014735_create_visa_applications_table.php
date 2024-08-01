<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\VisaApplication;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('visa_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('회원 마스터 일련번호');
            $table->unsignedBigInteger('consulting_user_id')->nullable()
                ->comment('컨설턴트 회원 마스터 일련번호');
            $table->unsignedBigInteger('attorney_user_id')->nullable()
                ->comment('비자 신청 업무 담당 행정사 회원 일련번호');
            $table->integer('order_stay_period')->comment('채류기간 구분');
            $table->string('order_stay_status')->comment('체류자격');
            $table->integer('status')->default(\App\Lib\VisaApplicationStatus::STATUS_REGISTERING->value)
                ->comment('진행 상태');
            $table->timestamps();
            $table->comment('사증 정보');

            // 제약조건 설정
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('consulting_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('attorney_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('visa_applications');
    }
};
