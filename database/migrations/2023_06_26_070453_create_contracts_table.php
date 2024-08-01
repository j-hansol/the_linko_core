<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Lib\ContractType;
use App\Lib\ContractStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_user_id')->comment('발주자 일련번호');
            $table->unsignedBigInteger('recipient_user_id')->comment('수주자 일련번호');
            $table->unsignedBigInteger('mediation_user_id')->nullable()->comment('중계자 일련번호');
            $table->unsignedBigInteger('sub_recipient_user_id')->nullable()->comment('중계 계약의 최종 수주자 계정 일련번호');
            $table->unsignedBigInteger('occupational_group_id')->comment('직업군 정보 일련번호');
            $table->integer('type')->default(ContractType::DIRECT->value)->comment('계약유형');
            $table->string('uuid', 40)->unique()->comment('계약 UUID');
            $table->string('title')->comment('계약제목');
            $table->text('body')->comment('계약내용');
            $table->string('sub_title')->comment('중계 계약 제목');
            $table->text('sub_body')->comment('중계 계약 내용');
            $table->unsignedInteger('worker_count')->default(0)->comment('요청 인력 수');
            $table->date('contract_date')->nullable()->comment('계약일');
            $table->date('sub_contract_date')->nullable()->comment('중계 계약일');
            $table->string('order_authentication', 2048)->nullable()->comment('발주자 서명');
            $table->string('recipient_authentication', 2048)->nullable()->comment('수주자 서명');
            $table->string('mediation_authentication', 2048)->nullable()->comment('중계자 서명');
            $table->integer('status')->default(ContractStatus::REGISTERED->value)->comment('상태정보');
            $table->timestamps();

            // 제약조건 설정
            $table->foreign('order_user_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('recipient_user_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('mediation_user_id')->references('id')->on('users')
                ->onDelete('set null');
            $table->foreign('sub_recipient_user_id')->references('id')->on('users')
                ->onDelete('set null');
            $table->foreign('occupational_group_id')->references('id')->on('occupational_groups')
                ->onDelete('cascade');
        });

        Schema::create('contract_managers', function(Blueprint $table) {
            $table->unsignedBigInteger('contract_id')->comment('계약정보 일련번호');
            $table->unsignedBigInteger('manager_user_id')->comment('근로자 관리자 계정 일련번호');
            $table->integer('type')->comment('관리 유형');
            $table->comment('발주처 관리기관 정보');

            // 제약조건 설정
            $table->primary(['contract_id', 'manager_user_id', 'type']);
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
            $table->foreign('manager_user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('worker_eval_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('contract_id')->comment('계약 정보 일련번호');
            $table->unsignedBigInteger('worker_eval_info_id')->comment('평가 정보 일련번호');
            $table->comment('근로자 평가 계획');

            // 제약조건 설정
            $table->primary(['contract_id', 'worker_eval_info_id']);
        });

        Schema::create('company_eval_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('contract_id')->comment('계약 정보 일련번호');
            $table->unsignedBigInteger('company_eval_info_id')->comment('평가 정보 일련번호');
            $table->comment('기업 평가 계획');

            // 제약조건 설정
            $table->primary(['contract_id', 'company_eval_info_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_eval_plans');
        Schema::dropIfExists('worker_eval_plans');
        Schema::dropIfExists('contract_managers');
        Schema::dropIfExists('contracts');
    }
};
