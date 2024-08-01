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
        Schema::table('visa_vist_details', function (Blueprint $table) {
            $table->string('text_intended_stay_period')->nullable()->after('intended_stay_period')->comment('체류 예정 기간 문자열');
            $table->string('text_intended_entry_date')->nullable()->after('intended_entry_date')->comment('입국 예정일자 문자열');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('visa_vist_details', function (Blueprint $table) {
            $table->dropColumn('text_intended_entry_date');
            $table->dropColumn('text_intended_stay_period');
        });
    }
};
