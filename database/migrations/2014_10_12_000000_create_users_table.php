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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();

            // 식별자 정보
            $table->string('id_alias', 20)->unique()->comment('별칭(사용자 ID)');
            $table->string('email')->nullable()->comment('이메일');
            $table->string('api_token', 80)->unique()->nullable()
                ->default(null)->comment('Api 토큰');

            // 공통 필드
            $table->boolean('is_organization')->default(0)->comment('단체 여부');
            $table->string('name')->comment('이름/상호');
            $table->unsignedBigInteger('country_id')->comment('국가 일련번호');
            $table->string('timezone')->nullable()->default('UTC')->comment('시간권');
            $table->string('photo', 512)->nullable()->comment('사진 또는 로고');
            $table->string('cell_phone', 40)->nullable()->comment('휴대전화');
            $table->string('address')->nullable()->comment('주소');

            // 개인 프로필
            $table->string('family_name')->nullable()->comment('성');
            $table->string('given_names')->nullable()->comment('명');
            $table->string('hanja_name')->nullable()->comment('한자성명');
            $table->string('identity_no', 40)->nullable()->comment('신분증 번호');
            $table->enum('sex', ['M', 'F'])->nullable()->comment('성별');
            $table->date('birthday')->nullable()->comment('생년월일');
            $table->unsignedBigInteger('birth_country_id')->nullable()->comment('출생국가');
            $table->json('another_nationality_ids')->nullable()->comment('이중국적');
            $table->string('old_family_name')->nullable()->comment('이전 입국시 성');
            $table->string('old_given_names')->nullable()->comment('이전 입국시 명');
            $table->unsignedBigInteger('management_org_id')->nullable()->comment('관리 조직 계정 일련번호');

            // 단체 프로필
            $table->string('registration_no', 40)->nullable()->comment('사업자등록 번호');
            $table->string('boss_name')->nullable()->comment('대표자 이름');
            $table->string('manager_name')->nullable()->comment('담당자 이름');
            $table->string('telephone')->nullable()->comment('전화번호');
            $table->string('fax')->nullable()->comment('팩스번호');
            $table->string('road_map', 512)->nullable()->comment('약도');
            $table->double('longitude')->nullable()->nullable()->comment('경도');
            $table->double('latitude')->nullable()->nullable()->comment('위도');

            // 인증관련 필드
            $table->integer('login_method')->default(\App\Lib\LoginMethod::LOGIN_METHOD_PASSWORD->value)->comment('로그인 방법');
            $table->string('password')->nullable()->comment('비밀번호');
            $table->string('auth_provider', 60)->nullable()->comment('SNS 로그인 서비스 구분');
            $table->string('auth_provider_identifier')->nullable()->comment('SNS 로그인 서비스 식별자');
            $table->boolean('active')->default(0)->index()->comment('활성화 여부');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();

            // 시스템 이용 정보
            $table->integer('create_year')->nullable()->comment('등록년도');
            $table->integer('create_month')->nullable()->comment('등록월');
            $table->integer('create_day')->nullable()->comment('동록일');
            $table->timestamps();
            $table->comment('회원 마스터');

            // 제약조건 설정
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('birth_country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('management_org_id')->references('id')->on('users')->onDelete('set null');
        });

        // 변경정보 저장용
        Schema::create('user_change_histories', function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('회원 일련번호');
            $table->text('origin')->comment('원본 내용');
            $table->text('changed')->comment('변경 내용');
            $table->timestamp('created_at')->comment('변경일시');
            $table->comment('회원정보 변경 내역');
        });

        // 허용 서버 IP 정보
        Schema::create('allowed_server_ips', function(Blueprint $table) {
            $table->string('ip_address')->primary()->comment('IP 주소');
            $table->unsignedBigInteger('user_id')->comment('회원 일련번호');
            $table->comment('허용된 서버 IP 정보');
        });

        Schema::create('managers', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_user_id')->comment('발주처 또는 인력 공급처 계정 일련번호');
            $table->unsignedBigInteger('manager_user_id')->comment('관리기간 계정 일련번호');
            $table->comment('단체의 관리기관 정보');

            // 제약조건 설정
            $table->primary(['organization_user_id', 'manager_user_id']);
            $table->foreign('organization_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('manager_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('managers');
        Schema::dropIfExists('allowed_server_ips');
        Schema::dropIfExists('user_change_histories');
        Schema::dropIfExists('users');
    }
};
