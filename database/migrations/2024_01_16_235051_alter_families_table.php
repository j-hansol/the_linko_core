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
        Schema::table('visa_families', function (Blueprint $table) {
            $table->string('spouse_nationality')->after('spouse_nationality_id')->comment('사용자 입력 배우자 국적')
                ->nullable()->comment('배우자 국적');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visa_families', function (Blueprint $table) {
            $table->dropColumn('spouse_nationality');
        });
    }
};
