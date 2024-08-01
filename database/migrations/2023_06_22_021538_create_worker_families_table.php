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
        Schema::create('worker_families', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('회원 일련번호');
            $table->unsignedBigInteger('country_id')->comment('국적 (국가 일련번호)');
            $table->string('name')->comment('이름');
            $table->string('birthday')->nullable()->comment('생년월일');
            $table->string('relationship')->comment('관계');
            $table->unsignedBigInteger('reference_count')->default(0)->comment('참조 회수');
            $table->timestamps();
            $table->comment('근로자 가족 정보');

            // 제약조건 설정
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_families');
    }
};
