<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('visa_applications', function (Blueprint $table) {
            $table->json('invalid_fields')->nullable()->after('status')->comment('검토가 필요한 필드 목록');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('visa_applications', function (Blueprint $table) {
            $table->dropColumn('invalid_fields');
        });
    }
};
