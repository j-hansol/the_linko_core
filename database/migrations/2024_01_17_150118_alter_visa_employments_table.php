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
        Schema::table('visa_employments', function (Blueprint $table) {
            $table->string('other_detail')->after('job')->nullable()->comment('기타 상세 내용');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visa_employments', function (Blueprint $table) {
            $table->dropColumn('other_detail');
        });
    }
};
