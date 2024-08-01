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
        Schema::create('worker_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('회원 일련번호');
            $table->unsignedBigInteger('country_id')->comment('방문국가 일련번호');
            $table->string('visit_purpose')->comment('방문목적');
            $table->date('entry_date')->nullable()->comment('방문일자');
            $table->date('departure_date')->nullable()->comment('출국일자');
            $table->unsignedBigInteger('reference_count')->default(0)->comment('참조 회수');
            $table->timestamps();
            $table->comment('한국 방문 내역');

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
        Schema::dropIfExists('worker_visits');
    }
};
