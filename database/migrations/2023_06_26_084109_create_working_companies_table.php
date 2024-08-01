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
        Schema::create('working_companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id')->comment('계약 정보 일련번호');
            $table->unsignedBigInteger('company_user_id')->comment('수요 기업 계정 일련번호');
            $table->unsignedInteger('planned_worker_count')->default(0)->comment('계획 근로자 인원 수');
            $table->unsignedInteger('assigned_worker_count')->default(0)->comment('배정된 근로자 인원 수');
            $table->comment('근무(수요)기업 및 배정 요약 정보');

            // 제약조건 설정
            $table->foreign('contract_id')->references('id')->on('contracts')
                ->onDelete('cascade');
            $table->foreign('company_user_id')->references('id')->on('users')
                ->onDelete('cascade');
        });

        Schema::create('worker_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('contract_id')->comment('계약 정보 일련번호');
            $table->unsignedBigInteger('company_user_id')->comment('수요 기업 계정 일련번호');
            $table->unsignedBigInteger('task_id')->comment('수요기업 업무정보 일련번호');
            $table->comment('담당 업무 정보');

            // 제약조건 설정
            $table->primary(['contract_id', 'company_user_id', 'task_id']);
            $table->foreign('contract_id')->references('id')->on('contracts')
                ->onDelete('cascade');
            $table->foreign('company_user_id')->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_tasks');
        Schema::dropIfExists('working_companies');
    }
};
