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
        Schema::create('visa_assistants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visa_application_id')->comment('비자 정보 일련번호');
            $table->unsignedBigInteger('user_id')->comment('회원 마스터 일련번호');
            $table->unsignedBigInteger('consulting_user_id')->nullable()->comment('컨설팅 행정사 일련번호');
            $table->string('assistant_name')->nullable()->comment('이름');
            $table->date('assistant_birthday')->nullable()->comment('생년월일');
            $table->string('assistant_telephone', 40)->nullable()->comment('전화번호');
            $table->string('assistant_relationship')->nullable()->comment('관계');
            $table->timestamps();
            $table->comment('서류작성 도움 정보');

            // 제약조건 설정
            $table->foreign('visa_application_id')->references('id')->on('visa_applications')
                ->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('consulting_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_assistants');
    }
};
