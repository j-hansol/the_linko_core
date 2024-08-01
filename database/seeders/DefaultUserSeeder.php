<?php

namespace Database\Seeders;

use App\Lib\DeviceType;
use App\Lib\LoginMethod;
use App\Lib\MemberType;
use App\Models\Country;
use App\Models\Device;
use App\Models\PasswordHistory;
use App\Models\User;
use App\Models\UserType;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $password = Hash::make("Default@Sohocode#2024");
        $now = Carbon::now('Asia/Seoul');

        // 시스템 관리자 계정 생성
        $user = User::create([
            'id_alias' => 'OPR2023JAN010000',
            'email' => 'infra@sohocode.kr',
            'api_token' => User::genApiToken(),
            'is_organization' => 0,
            'name' => '시스템 관리자',
            'country_id' => Country::findByCode('KR')->id,
            'timezone' => 'Asia/Seoul',
            'cell_phone' => '+821087694355',
            'address' => '부산광역시 남구 신선로 365 부경대학교 용당캠퍼스 창업보육센터 401호',
            'family_name' => '관리자',
            'given_names' => '시스템',
            'login_method' => LoginMethod::LOGIN_METHOD_PASSWORD,
            'password' => $password,
            'active' => 1,
            'email_verified_at' => $now->format('Y-m-d H:i:s'),
            'create_year' => 2023,
            'create_month' => 1,
            'create_day' => 1
        ]);
        UserType::createType($user, MemberType::TYPE_OPERATOR);
        PasswordHistory::createByUser($user, $password);
        Device::createFixedDevice($user);

        // 중개계약을 위한 중개사 계정 생성
        $user = User::create([
            'id_alias' => 'INM2023JAN010000',
            'email' => 'manager@sohocode.kr',
            'api_token' => User::genApiToken(),
            'is_organization' => 1,
            'name' => '주식회사 소호코드',
            'country_id' => Country::findByCode('KR')->id,
            'timezone' => 'Asia/Seoul',
            'cell_phone' => '+821050684355',
            'address' => '부산광역시 남구 신선로 365 부경대학교 용당캠퍼스 창업보육센터 401호',
            'registration_no' => '160-87-02427',
            'boss_name' => '고상혁',
            'manager_name' => '고상혁',
            'telephone' => '+821050684355',
            'login_method' => LoginMethod::LOGIN_METHOD_PASSWORD,
            'password' => $password,
            'active' => 1,
            'email_verified_at' => $now->format('Y-m-d H:i:s'),
            'create_year' => 2023,
            'create_month' => 1,
            'create_day' => 1
        ]);
        UserType::createType($user, MemberType::TYPE_INTERMEDIARY);
        UserType::createType($user, MemberType::TYPE_MANAGER);
        UserType::createType($user, MemberType::TYPE_ORDER);
        UserType::createType($user, MemberType::TYPE_RECIPIENT);
        PasswordHistory::createByUser($user, $password);
        Device::createFixedDevice($user);
    }
}
