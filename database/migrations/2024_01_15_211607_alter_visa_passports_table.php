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
        Schema::table('visa_passports', function (Blueprint $table) {
            $table->json('scanned_data')->after('other_passport_expire_date')->nullable()->comment('스켄된 여권 정보');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visa_passports', function (Blueprint $table) {
            $table->dropColumn('scanned_data');
        });
    }
};
