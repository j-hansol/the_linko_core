<?php

namespace App\Traits\Common;

use App\DTOs\V1\PreSaveWorkerDto;
use App\Events\AccountCreated;
use App\Lib\CryptDataB64 as CryptData;
use App\Lib\LoginMethod;
use App\Lib\MemberType;
use App\Models\Country;
use App\Models\Device;
use App\Models\PasswordHistory;
use App\Models\PreSaveWorkerInfo;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait CreateWorkerAccount {
    /**
     * 지정 데이터로 게정을 생성 가능한 경우 생성한다.
     * @param PreSaveWorkerDto $dto
     * @param User $manager
     * @param MemberType $initial_user_type
     * @return User|null
     */
    private function _createAccount(PreSaveWorkerDto $dto, User $manager, MemberType $initial_user_type) : ?User {
        $id_info = User::genInitialTemporaryIdAlias($initial_user_type->value);
        $country = Country::findMe($manager->country_id);
        $password = $data['password'] ?? Str::random(20);
        $hashed_password = Hash::make($password);
        $etc = [
            'email' => $dto->getEmail(),
            'api_token' => User::genApiToken(),
            'login_method' => LoginMethod::LOGIN_METHOD_PASSWORD->value,
            'password' => Hash::make($password),
            'active' => 1,
            'is_organization' => 0,
            'management_org_id' => $manager->id,
        ];
        $profile = [
            'name' => User::getPersonName($country, $dto->getFamilyName(), $dto->getGivenNames()),
            'country_id' => $country->id,
            'cell_phone' => $dto->getCellPhone(),
            'address' => $dto->getAddress(),
            'family_name' => $dto->getFamilyName(),
            'given_names' => $dto->getGivenNames(),
            'hanja_name' => $dto->getHanjaName(),
            'identity_no' => $dto->getIdentityNo(),
            'sex' => $dto->getSex(),
            'birthday' => $dto->getBirthday()->format('Y-m-d'),
            'birth_country_id' => $country->id,
            'old_family_name' => $dto->getOldFamilyName(),
            'old_given_names' => $dto->getOldGivenNames(),
        ];
        $user = User::create($id_info + $profile + $etc);
        $user->createInitialPassword($manager, CryptData::encrypt($password));
        PasswordHistory::createByUser($user, $hashed_password);
        UserType::createType($user, $initial_user_type);
        Device::createFixedDevice($user);
        AccountCreated::dispatch($user);
        return $user;
    }

    private function _createPreSaveWorkerInfo(PreSaveWorkerDto $dto, User $manager, User $user = null) : ?PreSaveWorkerInfo {
        return PreSaveWorkerInfo::create([
            'user_id' => $user?->id,
            'management_org_id' => $this->manager->id,
            'email' => $dto->getEmail(),
            'cell_phone' => $dto->getCellPhone(),
            'address' => $dto->getAddress(),
            'family_name' => $dto->getFamilyName(),
            'given_names' => $dto->getGivenNames(),
            'hanja_name' => $dto->getHanjaName(),
            'identity_no' => $dto->getIdentityNo(),
            'sex' => $dto->getSex(),
            'birthday' => $dto->getBirthday()->format('Y-m-d'),
            'old_family_name' => $dto->getOldFamilyName(),
            'old_given_names' => $dto->getOldGivenNames()
        ]);
    }

    /**
     * 기존 근로자 임시 데이터를 변경한다.
     * @param PreSaveWorkerDto $dto
     * @param PreSaveWorkerInfo $info
     * @param User $user
     * @return void
     */
    private function _updatePreSaveWorkerInfo(PreSaveWorkerDto $dto, PreSaveWorkerInfo $info, User $user) : void {
        $info->email = $dto->getEmail();;
        $info->cell_phone = $dto->getCellPhone();;
        $info->address = $dto->getAddress();;
        $info->family_name = $dto->getFamilyName();;
        $info->given_names = $dto->getGivenNames();;
        $info->hanja_name = $dto->getHanjaName();;
        $info->identity_no = $dto->getIdentityNo();;
        $info->sex = $dto->getSex();;
        $info->birthday = $dto->getBirthday()->format('Y-m-d');
        $info->old_family_name = $dto->getOldFamilyName();;
        $info->old_given_names = $dto->getOldGivenNames();
        $info->user_id = $user?->id;
        $info->save();
    }
}
