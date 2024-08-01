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
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id')->comment('국가정보 일련번호');
            $table->mediumText('terms')->comment('약관 내용');
            $table->timestamps();
            $table->comment('이용약관');

            // 제약조건 설정
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
        });

        // 번역정보 저장용
        Schema::create('terms_languages', function (Blueprint $table) {
            $table->unsignedBigInteger('term_id')->comment('약관 일련번호');
            $table->string('language_code', 10)->index()->comment('언어 코드');
            $table->mediumText('terms')->comment('약관 내용');
            $table->comment('이용약관 번역정보');

            // 제약조건 설정
            $table->primary(['term_id', 'language_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terms_languages');
        Schema::dropIfExists('terms');
    }
};
