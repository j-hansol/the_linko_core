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
        Schema::create('entry_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id')->comment('계약 정보 일련번호');
            $table->date('entry_date')->comment('입국일자');
            $table->unsignedInteger('entry_limit')->default(0)->comment('입국정원');
            $table->dateTime('target_datetime')->nullable()->comment('최종 목적지 집결 일시');
            $table->string('target_place')->nullable()->comment('최종 목적지');
            $table->comment('입국일정');

            // 제약조건 설정
            $table->foreign('contract_id')->references('id')->on('contracts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry_schedules');
    }
};
