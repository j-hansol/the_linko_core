<?php

use App\Lib\BodyType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('worker_body_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('회원 일련번호');
            $table->integer('type')->default(BodyType::ALL->value)->comment('사진 유형');
            $table->string('origin_name')->comment('원본 파일명');
            $table->string('file_path', 512)->comment('파일 저장 경로');
            $table->timestamps();
            $table->comment('근로자 신체 사진 정보');

            // 제약조건 설정
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('worker_body_photos');
    }
};
