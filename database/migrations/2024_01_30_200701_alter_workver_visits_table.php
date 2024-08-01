<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('worker_visits', function (Blueprint $table) {
            $table->string('period_of_stay')->nullable()->after('departure_date')->comment('체류 기간');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('worker_visits', function (Blueprint $table) {
            $table->dropColumn('period_of_stay');
        });
    }
};
