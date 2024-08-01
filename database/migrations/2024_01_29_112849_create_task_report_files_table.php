<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('task_report_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_report_id')->comment('업무 수행 보고 일련번호');
            $table->string('origin_name')->comment('파일명');
            $table->string('file_path', 512)->comment('저장경로');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('task_report_files');
    }
};
