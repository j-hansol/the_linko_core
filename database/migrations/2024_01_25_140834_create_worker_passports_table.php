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
        Schema::create('worker_passports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('사용자 계정 일련번호');
            $table->string('passport_no', 40)->unique()->comment('여권번호');
            $table->unsignedBigInteger('passport_country_id')->nullable()->comment('발급국가');
            $table->string('country_iso3_code', 4)->nullable()->index()->comment('국가코드 (ISO 3문자)');
            $table->string('country_name')->nullable()->comment('발급 국가명');
            $table->string('family_name')->comment('이름(성)');
            $table->string('middle_name')->nullable()->comment('중간이름');
            $table->string('given_names')->comment('이름');
            $table->date('birthday')->comment('생년월일');
            $table->string('birth_place')->nullable()->comment('출생지');
            $table->string('sex')->comment('성별');
            $table->string('issue_place')->comment('발급지');
            $table->date('issue_date')->comment('발급일자');
            $table->date('expire_date')->comment('만료일자');
            $table->string('file_path', 512)->nullable()->comment('파일 저장 경로');
            $table->timestamps();

            // 제약 조건 설정
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('passport_country_id')->references('id')->on('countries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_passports');
    }
};
