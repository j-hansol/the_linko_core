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
        Schema::create('privacy_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id')->comment('국가정보 일련번호');
            $table->mediumText('policy')->comment('개인정보 처리방침');
            $table->timestamps();
            $table->comment('개언정보처리 방침');

            // 제약조건 설정
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
        });

        // 번역정보 저장용
        Schema::create('privacy_polices_languages', function (Blueprint $table) {
            $table->unsignedBigInteger('privacy_policy_id')->comment('개언정보처리방침 일련번호');
            $table->string('language_code', 10)->index()->comment('언어 코드');
            $table->mediumText('policy')->comment('개인정보 처리방침');
            $table->comment('개언정보처리 방침 번역정보');

            // 제약조건 설정
            $table->primary(['privacy_policy_id', 'language_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('privacy_polices_languages');
        Schema::dropIfExists('privacy_policies');
    }
};
