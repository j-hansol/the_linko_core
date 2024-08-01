<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration  {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('worker_experiences', function (Blueprint $table) {
            $table->text('job_description')->after('position')->nullable()->comment('업무 설명');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('worker_experiences', function (Blueprint $table) {
            $table->dropColumn('job_description');
        });
    }
};
