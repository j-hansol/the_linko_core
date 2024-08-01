<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Lib\AssignedWorkerStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assigned_workers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id')->comment('계약 정보 일련번호');
            $table->unsignedBigInteger('company_user_id')->nullable()->comment('배정 수요 기업 계정 일련번호');
            $table->unsignedBigInteger('worker_user_id')->comment('근로자 계정 일련번호');
            $table->unsignedBigInteger('manager_operator_user_id')->nullable()
                ->comment('근로자 관리 담당자 계정 일련번호');
            $table->unsignedBigInteger('attorney_user_id')->nullable()->comment('담당 행정사 일련번호');
            $table->unsignedBigInteger('entry_schedule_id')->nullable()->comment('입국일정 정보 일련번호');
            $table->integer('status')->default(AssignedWorkerStatus::REGISTERED->value)->comment('상태');
            $table->timestamps();

            // 제약조건 설정
            $table->foreign('contract_id')->references('id')->on('contracts')
                ->onDelete('cascade');
            $table->foreign('company_user_id')->references('id')->on('users')
                ->onDelete('set null');
            $table->foreign('worker_user_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('manager_operator_user_id')->references('id')->on('users')
                ->onDelete('set null');
            $table->foreign('attorney_user_id')->references('id')->on('users')
                ->onDelete('set null');
            $table->foreign('entry_schedule_id')->references('id')->on('entry_schedules')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assigned_workers');
    }
};
