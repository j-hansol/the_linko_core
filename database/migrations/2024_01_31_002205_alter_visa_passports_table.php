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
        Schema::table('visa_passports', function (Blueprint $table) {
            $table->string('text_issue_date')->nullable()->after('issue_date')->comment('발급일자 문자열');
            $table->string('text_expire_date')->nullable()->after('expire_date')->comment('만료일자 문자열');
            $table->string('text_other_passport_expire_date')->nullable()->after('other_passport_expire_date')->comment('다른 여권 만료일자 문자열');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('visa_passports', function (Blueprint $table) {
            $table->dropColumn('text_other_passport_expire_date');
            $table->dropColumn('text_expire_date');
            $table->dropColumn('text_issue_date');
        });
    }
};
