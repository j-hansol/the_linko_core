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
        Schema::table('worker_families', function (Blueprint $table) {
            $table->string('text_birthday')->nullable()->after('birthday')->comment('생년월일 문자열');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('worker_families', function (Blueprint $table) {
            $table->dropColumn('text_birthday');
        });
    }
};

