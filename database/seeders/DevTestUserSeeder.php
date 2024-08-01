<?php

namespace Database\Seeders;

use App\Lib\LoginMethod;
use App\Lib\MemberType;
use App\Models\Country;
use App\Models\Device;
use App\Models\PasswordHistory;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DevTestUserSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $password = 'Dev@Sohocode#2024';

        // 개발용 해외 기관 계정 생성
        $manager = $this->_createDevUser([
            'email' => 'f01@fake.com',
            'name' => 'Foreign Manager',
            'country_id' => Country::findByCode('PH')->id,
            'cell_phone' => '+63123456',
            'address' => 'Temp Address',
            'boss_name' => 'Temp',
            'manager_name' => 'Temp',
            'registration_no' => '1234567',
            'password' => $password,
        ], MemberType::TYPE_FOREIGN_MANAGER);

        // 개발용 해외 기관 실무자 계정 생성
        $this->_createDevUser([
            'email' => 'f02@fake.com',
            'name' => 'Foreign Manager Operator',
            'country_id' => Country::findByCode('PH')->id,
            'cell_phone' => '+631234567',
            'address' => 'Temp Address',
            'family_name' => 'Temp Family name',
            'given_names' => 'Temp Given names',
            'identity_no' => '123456789',
            'sex' => 'M',
            'birthday' => '1970-10-01',
            'password' => $password,
        ], MemberType::TYPE_FOREIGN_MANAGER_OPERATOR, $manager->id);

        // 해외 기관 소속 근로자 계정 생성
        $this->_createDevUser([
            'email' => 'f03@fake.com',
            'name' => 'Foreign Worker',
            'country_id' => Country::findByCode('PH')->id,
            'cell_phone' => '+6312345678',
            'address' => 'Temp Address',
            'family_name' => 'Worker Family name',
            'given_names' => 'Worker Given names',
            'identity_no' => '1234567890',
            'sex' => 'M',
            'birthday' => '1970-10-10',
            'password' => $password,
        ], MemberType::TYPE_FOREIGN_PERSON, $manager->id);

        // 국내 지자체 계정 생성
        $government = $this->_createDevUser([
            'email' => 'f04@fake.com',
            'name' => '부산광역시 (국내 지자체)',
            'country_id' => Country::findByCode('KR')->id,
            'cell_phone' => '+82123456',
            'address' => '부산광역시',
            'boss_name' => '박형준',
            'manager_name' => '홍길동',
            'registration_no' => '6131234567',
            'password' => $password,
        ], MemberType::TYPE_GOVERNMENT);

        // 해외 지자체 계정 생성
        $f_government = $this->_createDevUser([
            'email' => 'f05@fake.com',
            'name' => 'COPIZ (해외 지자체)',
            'country_id' => Country::findByCode('PH')->id,
            'cell_phone' => '+63123456',
            'address' => 'PH Temp',
            'boss_name' => 'PH Temp',
            'manager_name' => 'Temp',
            'registration_no' => '121234567',
            'password' => $password,
        ], MemberType::TYPE_FOREIGN_GOVERNMENT);

        // 국내 관리기관
        $ko_manager = $this->_createDevUser([
            'email' => 'f06@fake.com',
            'name' => '소호코드넷 (국내 관리기관)',
            'country_id' => Country::findByCode('KR')->id,
            'cell_phone' => '+821123456',
            'address' => '부산광역시',
            'boss_name' => '고무진',
            'manager_name' => '홍길순',
            'registration_no' => '6131234567',
            'password' => $password,
        ], MemberType::TYPE_MANAGER);

        // 국내 관리기관 실무자
        $this->_createDevUser([
            'email' => 'f07@fake.com',
            'name' => '유관순 (국내 관리기관 실무자)',
            'country_id' => Country::findByCode('KR')->id,
            'cell_phone' => '+63123456457',
            'address' => '부산광역시',
            'family_name' => '유',
            'given_names' => '관순',
            'identity_no' => '1234569789',
            'sex' => 'M',
            'birthday' => '1970-10-01',
            'password' => $password,
        ], MemberType::TYPE_MANAGER_OPERATOR, $ko_manager->id);

        // 국내 행정사 계정 생성
        $this->_createDevUser([
            'email' => 'f08@fake.com',
            'name' => '임꺽정 행정사',
            'country_id' => Country::findByCode('KR')->id,
            'cell_phone' => '+8212123456',
            'address' => '부산광역시',
            'boss_name' => '임꺽정',
            'manager_name' => '홍길호',
            'registration_no' => '61131234567',
            'password' => $password,
        ], MemberType::TYPE_ATTORNEY);

        // 국내 기업
        $this->_createDevUser([
            'email' => 'f09@fake.com',
            'name' => '빅테크 (기업)',
            'country_id' => Country::findByCode('KR')->id,
            'cell_phone' => '+82121233456',
            'address' => '부산광역시',
            'boss_name' => '김대호',
            'manager_name' => '홍소호',
            'registration_no' => '611131234567',
            'password' => $password,
        ], MemberType::TYPE_COMPANY);
    }

    /**
     * 개발울 위한 임시 계정을 생성한다.
     * @param array $data
     * @param MemberType $type
     * @param int|null $organization_id
     * @return User|null
     */
    private function _createDevUser(array $data, MemberType $type, ?int $organization_id = null) : ?User {
        $id_info = User::genInitialTemporaryIdAlias($type->value);
        $hashed_password = Hash::make($data['password']);
        $now = Carbon::now();

        $etc = [
            'api_token' => User::genApiToken(),
            'login_method' => LoginMethod::LOGIN_METHOD_PASSWORD->value,
            'active' => 1,
            'timezone' => 'UTC',
            'is_organization' => ($type->checkForeignOrganization() || $type->checkKoreaOrganization()) ? 1 : 0,
            'email_verified_at' => $now->format('Y-m-d H:i:s')
        ];
        if($organization_id) $etc['management_org_id'] = $organization_id;
        $user = User::create($id_info + $data + $etc);
        PasswordHistory::createByUser($user, $hashed_password);

        $types = match ($type) {
            MemberType::TYPE_GOVERNMENT => [
                MemberType::TYPE_GOVERNMENT,
                MemberType::TYPE_ORDER,
                MemberType::TYPE_COMPANY
            ],
            MemberType::TYPE_FOREIGN_GOVERNMENT => [
                MemberType::TYPE_FOREIGN_GOVERNMENT,
                MemberType::TYPE_RECIPIENT
            ],
            MemberType::TYPE_ATTORNEY => [MemberType::TYPE_ATTORNEY],
            MemberType::TYPE_COMPANY => [MemberType::TYPE_COMPANY],
            MemberType::TYPE_MANAGER => [MemberType::TYPE_MANAGER],
            MemberType::TYPE_FOREIGN_MANAGER => [MemberType::TYPE_FOREIGN_MANAGER],
            MemberType::TYPE_MANAGER_OPERATOR => [MemberType::TYPE_MANAGER_OPERATOR],
            MemberType::TYPE_FOREIGN_MANAGER_OPERATOR => [MemberType::TYPE_FOREIGN_MANAGER_OPERATOR],
            MemberType::TYPE_FOREIGN_PERSON => [MemberType::TYPE_FOREIGN_PERSON],
            default => [MemberType::TYPE_NONE]
        };

        foreach($types as $t) if($t != MemberType::TYPE_NONE) UserType::createType($user, $type);
        Device::createFixedDevice($user);
        return $user;
    }
}
