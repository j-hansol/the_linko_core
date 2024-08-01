<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration  {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('eval_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('eval_info_id')->comment('평가 정보 일련번호');
            $table->integer('type')->default(\App\Lib\QuestionType::TYPE_FIVE_START->value)->comment('평가 정보 유형');
            $table->string('question')->comment('질문');
            $table->string('answers', 2048)->nullable()->comment('선택 가능 답안');
            $table->timestamps();
            $table->comment('평가 항목 정보');

            // 제약 조건 설정
            $table->foreign('eval_info_id')->references('id')->on('eval_infos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eval_items');
    }
};
