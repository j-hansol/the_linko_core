<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration  {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('recommended_workers', function (Blueprint $table) {
            $table->unsignedBigInteger('status')->nullable()->after('worker_user_id')->comment('추천 근로자 상태');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('recommended_workers', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
