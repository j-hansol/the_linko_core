<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration  {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('eval_infos', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('평가 설문 제목');
            $table->integer('target')->index()->comment('평가 대상');
            $table->text('description')->nullable()->comment('요약 설명');
            $table->integer('items')->default(0)->comment('설문 항목 수');
            $table->boolean('active')->default(0)->index()->comment('현재 사용 여부');
            $table->timestamps();
            $table->comment('평가 설문 마스터 정보');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('eval_infos');
    }
};
