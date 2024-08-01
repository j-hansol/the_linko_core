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
        Schema::create('pre_save_worker_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('회원 계정 일련번호');
            $table->unsignedBigInteger('management_org_id')->comment('관리 조직 계정 일련번호');
            $table->string('email')->comment('이메일 주소');
            $table->string('cell_phone')->comment('휴대전화');
            $table->string('address')->nullable()->comment('주소');
            $table->string('family_name')->comment('성');
            $table->string('given_names')->comment('이름');
            $table->string('hanja_name')->nullable()->comment('한자 이름');
            $table->string('identity_no')->nullable()->comment('신분증 번호');
            $table->enum('sex', ['M', 'F'])->nullable()->comment('성별');
            $table->date('birthday')->nullable()->comment('생년월일');
            $table->string('old_family_name')->nullable()->comment('이전 입국시 성');
            $table->string('old_given_names')->nullable()->comment('이전 입국시 명');
            $table->timestamps();
            $table->comment('임시 저장 근로자 정보');

            // 제약조건 설정
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('management_org_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_save_worker_infos');
    }
};
