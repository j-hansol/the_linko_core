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
        Schema::create('worker_action_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id')->comment('계약정보 일련번호');
            $table->unsignedBigInteger('assigned_worker_id')->comment('배정 근로자 정보 일련번호');
            $table->unsignedBigInteger('company_user_id')->comment('기업 계정 일련번호');
            $table->unsignedBigInteger('worker_id')->comment('근로자 계정 일련번호');
            $table->unsignedBigInteger('author_user_id')->comment('등록자 계정 일련번호');
            $table->integer('type')->index()->comment('활동지점 유형');
            $table->string('name')->comment('지점 이름');
            $table->string('address')->comment('주소');
            $table->double('longitude')->comment('경도');
            $table->double('latitude')->comment('위도');
            $table->double('radius')->comment('활동반경');
            $table->timestamps();
            $table->comment('근로자 활동지점 정보');

            // 제약조건 설정
            $table->unique(['assigned_worker_id', 'type']);
            $table->foreign('contract_id')->references('id')
                ->on('contracts')->onDelete('cascade');
            $table->foreign('assigned_worker_id')->references('id')
                ->on('assigned_workers')->onDelete('cascade');
            $table->foreign('company_user_id')->references('id')
                ->on('users')->onDelete('cascade');
            $table->foreign('worker_id')->references('id')
                ->on('users')->onDelete('cascade');
            $table->foreign('author_user_id')->references('id')
                ->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('worker_action_points');
    }
};
