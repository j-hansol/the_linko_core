<?php

namespace App\DTOs\V1;

use App\Lib\CryptDataB64 as CryptData;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PersonProfileDto {
    // 속성
    private ?string $hanja_name;
    private ?array $another_nationality_ids;
    private ?string $old_family_name;
    private ?string $old_given_names;
    private ?int $management_org_id;

    // 생성자
    function __construct(
        private readonly string $family_name,
        private readonly string $given_names,
        private readonly string $identity_no,
        private readonly string $sex,
        private readonly Carbon $birthday,
        private readonly int $birth_country_id,
    ) {}

    // Setter, Getter
    public function getFamilyName() : string {return $this->family_name;}
    public function getGivenNames() : string {return $this->given_names;}
    public function getIdentityNo() : string {return $this->identity_no;}
    public function getSex() : string {return $this->sex;}
    public function getBirthday() : Carbon {return $this->birthday;}
    public function getBirthdayCountryId() : string {return $this->birth_country_id;}
    public function setHanjaName(?string $name) : void {$this->hanja_name = $name;}
    public function getHanjaName() : ?string {return $this->hanja_name;}
    public function setAnotherNationalityIds(?array $ids) : void {$this->another_nationality_ids = $ids;}
    public function getAnotherNationalityIds() : ?array {return $this->another_nationality_ids;}
    public function setOldFamilyName(?string $name) : void {$this->old_family_name = $name;}
    public function getOldFamilyName() : ?string {return $this->old_family_name;}
    public function setOldGivenNames(?string $name) : void {$this->old_given_names = $name;}
    public function getOldGivenNames() : ?string {return $this->old_given_names;}
    public function setManagementOrgId(?int $id) : void {$this->management_org_id = $id;}
    public function getManagementOrgId() : ?int {return $this->management_org_id;}

    // Creator

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return PersonProfileDto
     */
    public static function createFromRequest(Request $request) : PersonProfileDto {
        $dto = new static(
            $request->input('family_name'),
            $request->input('given_names'),
            CryptData::decrypt($request->input('identity_no'), 'identity_no'),
            $request->input('sex'),
            $request->date('birthday', 'Y-m-d'),
            $request->integer('birth_country_id')
        );
        $dto->setHanjaName($request->input('hanja_name'));
        $dto->setAnotherNationalityIds(convert_int_array($request->input('another_nationality_ids')));
        $dto->setOldFamilyName($request->input('old_family_name'));
        $dto->setOldGivenNames($request->input('old_given_names'));
        $dto->setManagementOrgId($request->input('management_org_id'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'family_name' => $this->family_name,
            'given_names' => $this->given_names,
            'hanja_name' => $this->hanja_name,
            'identity_no' => $this->identity_no,
            'sex' => $this->sex,
            'birthday' => $this->birthday->format('Y-m-d'),
            'birth_country_id' => $this->birth_country_id,
            'another_nationality_ids' => json_encode($this->another_nationality_ids),
            'old_family_name' => $this->old_family_name,
            'old_given_names' => $this->old_given_names,
            'management_org_id' => $this->management_org_id,
        ];
    }
}
