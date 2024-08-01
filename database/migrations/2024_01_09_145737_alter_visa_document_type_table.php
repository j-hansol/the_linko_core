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
        Schema::table('visa_document_types', function (Blueprint $table) {
            $table->boolean('required')->default(0)->index()->comment('필수 여부');
            $table->integer('weight')->default(0)->comment('가중치');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visa_document_types', function (Blueprint $table) {
            $table->dropColumn('weight');
            $table->dropColumn('required');
        });
    }
};
