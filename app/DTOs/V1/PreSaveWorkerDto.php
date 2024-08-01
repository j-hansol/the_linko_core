<?php

namespace App\DTOs\V1;

use App\Lib\CryptDataB64 as CryptData;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PreSaveWorkerDto {
    // 속상
    private ?string $address;
    private ?string $hanja_name;
    private ?string $identity_no;
    private ?string $old_family_name;
    private ?string $old_given_names;
    private ?User $user = null;


    // Getter, Setter
    public function getEmail() : string {return $this->email;}
    public function getCellPhone() : string {return $this->cell_phone;}
    public function setAddress(?string $address) : void {$this->address = $address;}
    public function getAddress() : ?string {return $this->address;}
    public function getFamilyName() : string {return $this->family_name;}
    public function getGivenNames() : string {return $this->given_names;}
    public function setHanjaName(?string $name) : void {$this->hanja_name = $name;}
    public function getHanjaName() : ?string {return $this->hanja_name;}
    public function setIdentityNo(?string $identity_no) : void {$this->identity_no = $identity_no;}
    public function getIdentityNo() : ?string {return $this->identity_no;}
    public function getSex() : string {return $this->sex;}
    public function getBirthday() : Carbon {return $this->birthday;}
    public function setOldFamilyName(?string $name) : void {$this->old_family_name = $name;}
    public function getOldFamilyName() : ?string {return $this->old_family_name;}
    public function setOldGivenNames(?string $name) : void {$this->old_given_names = $name;}
    public function getOldGivenNames() : ?string {return $this->old_given_names;}
    public function getCreateAccount() : bool {return $this->create_account;}

    // Creator
    function __construct(
        private readonly string $email,
        private readonly string $cell_phone,
        private readonly string $family_name,
        private readonly string $given_names,
        private readonly string $sex,
        private readonly Carbon $birthday,
        private readonly bool $create_account
    ) {}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return PreSaveWorkerDto
     */
    public static function createFromRequest(Request $request) : PreSaveWorkerDto {
        $dto = new static(
            CryptData::decrypt($request->input('email'), 'email'),
            CryptData::decrypt($request->input('cell_phone'), 'cell_phone'),
            $request->input('family_name'),
            $request->input('given_names'),
            $request->input('sex'),
            Carbon::createFromFormat('Y-m-d', $request->input('family_name')),
            $request->boolean('create_account')
        );
        $dto->setAddress(CryptData::decrypt($request->input('address'), 'address'));
        $dto->setHanjaName($request->input('hanja_name'));
        $dto->setIdentityNo(CryptData::decrypt($request->input('identity_no'), 'identity_no'));
        $dto->setOldFamilyName($request->input('old_family_name'));
        $dto->setOldGivenNames($request->input('old_given_names'));
        return $dto;
    }

    /**
     * 연관배열로부터 DTO 객체를 생성한다.
     * @param array $row
     * @param bool $create_account
     * @return $this|null
     */
    public static function createFromArray(array $row, bool $create_account = true) : ?PreSaveWorkerDto {
        $dto = new static(
            $row['email'] ?? null, $row['cell_phone'] ?? null, $row['family_name'] ?? null,
            $row['given_names'] ?? null, $row['sex'] ?? null,
            get_date_from_format($row['birthday'] ?? null), $create_account
        );
        $dto->setAddress($row['address'] ?? null);
        $dto->setHanjaName($row['hanja_name'] ?? null);
        $dto->setIdentityNo($row['identity_no'] ?? null);
        $dto->setOldFamilyName($row['old_family_name'] ?? null);
        $dto->setOldGivenNames($row['old_given_names'] ?? null);
        return $dto;
    }

    /**
     * 계정 생성 가능 여부를 판단한다.
     * @return bool
     */
    public function isCreatable() : bool {
        if($this->user) return false;
        else return ($this->user = User::findByEmail($this->email)) != null;
    }
}
