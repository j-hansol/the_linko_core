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
        Schema::table('visa_assistants', function (Blueprint $table) {
            $table->string('text_assistant_birthday')->nullable()->after('assistant_birthday')->comment('무너작성 도움이 생년월일 문자열');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('visa_assistants', function (Blueprint $table) {
            $table->dropColumn('text_assistant_birthday');
        });
    }
};
