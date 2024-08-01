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
        Schema::table('visa_contacts', function (Blueprint $table) {
            $table->string('email')->nullable()->comment('전자우편 주소');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('visa_contacts', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
