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
        Schema::create('visa_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visa_application_id')->comment('비자 정보 일련번호');
            $table->unsignedBigInteger('user_id')->comment('회원 마스터 일련번호');
            $table->string('family_name')->nullable()->comment('성');
            $table->string('given_names')->nullable()->comment('이름');
            $table->string('hanja_name')->nullable()->comment('한자 이름');
            $table->string('identity_no')->nullable()->comment('신분증 번호');
            $table->enum('sex', ['M', 'F'])->nullable()->comment('성별');
            $table->date('birthday')->nullable()->comment('생년월일');
            $table->unsignedBigInteger('nationality_id')->nullable()->comment('국적 (국가 일련번호)');
            $table->unsignedBigInteger('birth_country_id')->nullable()->comment('출생국가 일련번호');
            $table->json('another_nationality_ids')->nullable()->comment('복수 국적(이중국적)');
            $table->string('old_family_name')->nullable()->comment('이전 성');
            $table->string('old_given_names')->nullable()->comment('이전 이름');
            $table->timestamps();
            $table->comment('비자 프로필');

            // 제약조건 설정
            $table->foreign('visa_application_id')->references('id')->on('visa_applications')
                ->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('nationality_id')->references('id')->on('countries')
                ->onDelete('set null');
            $table->foreign('birth_country_id')->references('id')->on('countries')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_profiles');
    }
};
