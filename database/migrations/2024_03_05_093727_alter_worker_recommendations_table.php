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
        Schema::table('worker_recommendations', function (Blueprint $table) {
            $table->boolean('active')->after('expire_date')->comment('활성화 여부');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('worker_recommendations', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
