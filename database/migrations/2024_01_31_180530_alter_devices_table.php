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
        Schema::table('devices', function (Blueprint $table) {
            $table->string('ip_address', 40)->nullable()->comment('접속 IP 주소');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('ip_address');
        });
    }
};
