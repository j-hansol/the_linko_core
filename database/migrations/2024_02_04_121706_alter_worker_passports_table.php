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
        Schema::table('worker_passports', function (Blueprint $table) {
            $table->string('nationality')->nullable()->after('country_iso3_code')->comment('국적');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worker_passports', function (Blueprint $table) {
            $table->dropColumn('nationality');
        });
    }
};
