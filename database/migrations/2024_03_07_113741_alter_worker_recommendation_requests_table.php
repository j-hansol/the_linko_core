<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('worker_recommendation_requests', function (Blueprint $table) {
            $table->integer('status')->after('worker_count')->comment('처리 상태');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('worker_recommendation_requests', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
