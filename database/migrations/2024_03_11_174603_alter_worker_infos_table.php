<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use function Livewire\after;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('worker_infos', function (Blueprint $table) {
            $table->string('birth_place')->after('blood_type')->nullable()->comment('출생지역');
            $table->string('civil_status')->after('birth_place')->nullable()->comment('시민 신분');
            $table->string('religion')->after('civil_status')->nullable()->comment('종교');
            $table->string('language')->after('religion')->nullable()->comment('언어');
            $table->string('region')->after('language')->nullable()->comment('구역');
            $table->string('current_address')->after('region')->nullable()->comment('현 거주지');
            $table->string('spouse')->after('current_address')->nullable()->comment('배우자 이름');
            $table->string('children_names')->after('spouse')->nullable()->comment('자녀 이');
        });

        Schema::create('current_address_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->comment('계정 일련번호');
            $table->string('current_address')->comment('현 거주지 주소');
            $table->dateTime('created_at')->comment('생성 일시');
            $table->comment('현 거주지 변경 내역');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('current_address_histories');
        Schema::table('worker_infos', function (Blueprint $table) {
            $table->dropColumn('birth_place');
            $table->dropColumn('civil_status');
            $table->dropColumn('religion');
            $table->dropColumn('language');
            $table->dropColumn('region');
            $table->dropColumn('current_address');
            $table->dropColumn('spouse');
            $table->dropColumn('children_names');
        });
    }
};
