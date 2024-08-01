<?php

namespace App\DTOs\V1;

use App\Lib\CryptDataB64 as CryptData;
use App\Lib\MaritalStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FamilyDetailDto {
    // 속성
    private ?string $spouse_family_name;
    private ?string $spouse_given_name;
    private ?Carbon $spouse_birthday;
    private ?int $spouse_nationality_id;
    private ?string $spouse_residential_address;
    private ?string $spouse_contact_no;
    private int $number_of_children = 0;

    // Setter, Getter
    public function getMaritalStatus() : MaritalStatus {return $this->marital_status;}
    public function setSpouseFamilyName(?string $name) : void {$this->spouse_family_name = $name;}
    public function getSpouseFamilyName() : ?string {return $this->spouse_family_name;}
    public function setSpouseGivenNames(?string $name) : void {$this->spouse_given_name = $name;}
    public function getSpouseGivenNames() : ?string {return $this->spouse_given_name;}
    public function setSpouseBirthday(?Carbon $date) : void {$this->spouse_birthday = $date;}
    public function getSpouseBirthday() : ?Carbon {return $this->spouse_birthday;}
    public function setSpouseNationalityId(?int $id) : void {$this->spouse_nationality_id = $id;}
    public function getSpouseNationalityId() : ?int {return $this->spouse_nationality_id;}
    public function setSpouseResidentialAddress(?string $address) : void {$this->spouse_residential_address = $address;}
    public function getSpouseResidentialAddress() : ?string {return $this->spouse_residential_address;}
    public function setSpouseContactNo(?string $no) : void {$this->spouse_contact_no = $no;}
    public function getSpouseContactNo() : ?string {return $this->spouse_contact_no;}
    public function getNumberOfChildren() : int {return $this->number_of_children;}
    public function setNumberOfChildren(int $num = 0) :void {$this->number_of_children = $num ?? 0;}

    // Creator
    function __construct(
        private readonly MaritalStatus $marital_status,
    ) {}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return FamilyDetailDto
     */
    public static function createFromRequest(Request $request) : FamilyDetailDto {
        $dto = new static(
            $request->enum('marital_status', MaritalStatus::class),
        );
        $dto->setSpouseFamilyName($request->input('spouse_family_name'));
        $dto->setSpouseGivenNames($request->input('spouse_given_name'));
        $dto->setSpouseBirthday(
            $request->input('spouse_birthday') ?
                Carbon::createFromFormat('Y-m-d', $request->input('spouse_birthday')) : null);
        $dto->setSpouseNationalityId($request->input('spouse_nationality_id'));
        $dto->setSpouseResidentialAddress($request->input('spouse_residential_address') ? CryptData::decrypt($request->input('spouse_residential_address'), 'spouse_residential_address') : null);
        $dto->setSpouseContactNo($request->input('spouse_contact_no') ? CryptData::decrypt($request->input('spouse_contact_no'), 'spouse_contact_no') : null);
        $dto->setNumberOfChildren($request->integer('number_of_children'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'marital_status' => $this->marital_status->value,
            'spouse_family_name' => $this->spouse_family_name,
            'spouse_given_name' => $this->spouse_given_name,
            'spouse_birthday' => $this->spouse_birthday?->format('Y-m-d'),
            'spouse_nationality_id' => $this->spouse_nationality_id,
            'spouse_residential_address' => $this->spouse_residential_address,
            'spouse_contact_no' => $this->spouse_contact_no,
            'number_of_children' => $this->number_of_children,
        ];
    }
}
