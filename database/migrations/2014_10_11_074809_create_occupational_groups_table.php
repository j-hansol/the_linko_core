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
        Schema::create('occupational_groups', function (Blueprint $table) {
            $table->id();

            // 직업군 정보
            $table->string('group_code', 60)->unique()->comment('그룹쿄ㅗ드');
            $table->string('name')->comment('직업군 이름');
            $table->string('en_name')->nullable()->comment('직업군 이름 (영문)');
            $table->string('description')->nullable()->comment('직업군 설명');
            $table->string('en_description')->nullable()->comment('직업군 설명 (영문)');

            // 기타 정보
            $table->unsignedBigInteger('parent_id')->nullable()->comment('상위그룹 일련번호');
            $table->boolean('leaf_node')->default(0)->index()->comment('단말노드(직업) 여부');
            $table->boolean('active')->default(0)->index()->comment('활성(사용) 여부');
            $table->boolean('is_education_part')->default(0)->index()->comment('교육분야 설정 여부');
            $table->comment('직업군 및 희망 교육분야');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('occupational_groups');
    }
};
