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
        Schema::table('visa_profiles', function (Blueprint $table) {
            $table->string('nationality')->nullable()->after('nationality_id')->comment('사용자 입력 국정 이름');
            $table->string('another_nationality')->nullable()->after('another_nationality_ids')->comment('사용자 입력 다른 국적');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visa_profiles', function (Blueprint $table) {
            $table->dropColumn('nationality');
            $table->dropColumn('another_nationality');
        });
    }
};
