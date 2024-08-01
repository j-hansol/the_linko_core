<?php

namespace App\DTOs\V1;

use App\Lib\CryptDataB64 as CryptData;
use App\Models\Country;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WorkerAccountDto {
    // 속성
    private ?string $email = '';
    private ?string $cell_phone = '';
    private ?string $address = '';
    private ?string $identity_no;
    private ?string $hanja_name;
    private ?array $another_nationality_ids;
    private ?string $old_family_name;
    private ?string $old_given_names;
    private ?int $management_org_id;
    private ?string $password;
    private ?string $hashed_password;
    private ?int $birth_country_id;
    private ?Country $country;

    // Setter, Getter
    public function setEmail(?string $email) : void {$this->email = $email;}
    public function getEmail() : string {return $this->email;}
    public function getCountryId() : int {return $this->country_id;}
    public function setCellPhone(?string $number) : void {$this->cell_phone = $number;}
    public function getCellPhone() : string {return $this->cell_phone;}
    public function setAddress(?string $address) : void {$this->address = $address;}
    public function getAddress() : string {return $this->address;}
    public function getFamilyName() : string {return $this->family_name;}
    public function getGivenNames() : string {return $this->given_names;}
    public function setHanjaName(?string $name) : void {$this->hanja_name = $name;}
    public function getHanjaName() : ?string {return $this->hanja_name;}
    public function setIdentityNo(?string $identity_no) : void {$this->identity_no = $identity_no;}
    public function getIdentityNo() : string {return $this->identity_no;}
    public function getSex() : string {return $this->sex;}
    public function getBirthday() : Carbon {return $this->birthday;}
    public function setBirthCountryId(?int $birth_country_id) : void {$this->birth_country_id = $birth_country_id;}
    public function getBirthCountryId() : int {return $this->birth_country_id;}
    public function setAnotherNationalityIds(?array $ids) : void {$this->another_nationality_ids = $ids;}
    public function getAnotherNationalityIds() : ?array {return $this->another_nationality_ids;}
    public function setManagementOrgId(?int $id) : void {$this->management_org_id = $id;}
    public function getManagementOrgId() : ?int {return $this->management_org_id;}
    public function setPassword(?string $password) : void {
        $this->password = $password;
        $this->hashed_password = Hash::make($password);
    }
    public function getPassword() : ?string {return $this->password;}
    public function getHashedPassword() : ?string {return $this->hashed_password;}
    public function setOldFamilyName(?string $name) : void {$this->old_family_name = $name;}
    public function getOldFamilyName() : ?string {return $this->old_family_name;}
    public function setOldGivenNames(?string $name) : void {$this->old_given_names = $name;}
    public function getOldGivenNames() : ?string {return $this->old_given_names;}

    // Creator
    function __construct(
        private readonly int $country_id,
        private readonly string $family_name,
        private readonly string $given_names,
        private readonly string $sex,
        private readonly Carbon $birthday,
    ) {$this->country = Country::findMe($this->country_id);}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return WorkerAccountDto
     */
    public static function createFromRequest(Request $request) : WorkerAccountDto {
        $dto = new static(
            $request->input('country_id'),
            Str::upper($request->input('family_name')),
            Str::upper($request->input('given_names')),
            $request->input('sex'),
            Carbon::createFromFormat('Y-m-d', $request->input('birthday')),
        );
        $dto->setEmail(CryptData::decrypt($request->input('email'), 'email'));
        $dto->setAddress(CryptData::decrypt($request->input('address'), 'address'));
        $dto->setCellPhone(CryptData::decrypt($request->input('cell_phone'), 'cell_phone'));
        $dto->setIdentityNo(CryptData::decrypt($request->input('identity_no'), 'identity_no'));
        $dto->setAnotherNationalityIds(
            $request->input('another_nationality_ids') ?
                convert_int_array($request->input('another_nationality_ids')) : null);
        $dto->setPassword(CryptData::decrypt($request->input('password'), 'password'));
        $dto->setOldFamilyName($request->input('old_family_name'));
        $dto->setOldGivenNames($request->input('old_given_names'));
        $dto->setHanjaName($request->input('hanja_name'));
        $dto->setManagementOrgId($request->input('management_org_id'));
        $dto->setBirthCountryId($request->input('birth_country_id'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        $tr = [
            'email' => $this->email,
            'name' => User::getPersonName($this->country, $this->family_name, $this->given_names),
            'country_id' => $this->country_id,
            'cell_phone' => $this->cell_phone,
            'address' => $this->address,
            'family_name' => $this->family_name,
            'given_names' => $this->given_names,
            'hanja_name' => $this->hanja_name,
            'identity_no' => $this->identity_no,
            'sex' => $this->sex,
            'birthday' => $this->birthday->format('Y-m-d'),
            'birth_country_id' => $this->birth_country_id,
            'another_nationality_ids' => $this->another_nationality_ids ? json_encode($this->another_nationality_ids) : null,
            'old_family_name' => $this->old_family_name,
            'old_given_names' => $this->old_given_names,
        ];

        if($this->hashed_password) $tr['password'] = $this->hashed_password;
        return $tr;
    }
}
