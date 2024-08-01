<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration  {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('visa_document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('문서 이름');
            $table->string('en_name')->comment('문서 영문 이름');
            $table->string('description')->nullable()->comment('문서 설명');
            $table->string('en_description')->nullable()->comment('문서 영문 설명');
            $table->boolean('active')->default(0)->index()->comment('사용 여부');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('visa_document_types');
    }
};
