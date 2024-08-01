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
        Schema::table('order_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('target_manager_user_id')->after('order_user_id')->comment('대상 관리기관 계정 일련번호');
            $table->foreign('target_manager_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('order_tasks', function (Blueprint $table) {
            $table->dropForeign('order_tasks_target_manager_user_id_foreign');
            $table->dropColumn('target_manager_user_id');
        });
    }
};
