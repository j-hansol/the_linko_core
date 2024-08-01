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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index()->comment('국가명');
            $table->string('en_name')->index()->comment('국가명(영문)');
            $table->string('code')->unique()->comment('국가코드');
            $table->string('iso3_code')->nullable()->index()->comment('ISO 3문자 코드');
            $table->string('continent')->index()->comment('대륙명');
            $table->string('en_continent')->comment('en_continent');
            $table->string('language_code')->nullable()->comment('주 사용 언어 코드');
            $table->boolean('active')->default(0)->index()->comment('사용여부');
            $table->comment('국가정보');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
