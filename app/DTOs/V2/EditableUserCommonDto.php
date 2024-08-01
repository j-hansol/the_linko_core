<?php

namespace App\DTOs\V2;

use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;

class EditableUserCommonDto {
    // 속성
    private string $timezone = 'UTC';
    private ?string $address;

    // 생성자
    function __construct(
        private readonly string $email,
        private readonly bool $is_organization,
        private readonly string $name,
        private readonly int $country_id,
        private readonly string $cell_phone,
    ) {}

    // Setter, Getter
    public function getEmail() : string {return $this->email;}
    public function getIsOrganization() : bool {return $this->is_organization;}
    public function getName() : string {return $this->name;}
    public function getCountryId() : int {return $this->country_id;}
    public function setTimezone(?string $timezone) : void {$this->timezone = $timezone;}
    public function getTimezone() : ?string {return $this->timezone;}
    public function getCellPhone() : string {return $this->cell_phone;}
    public function setAddress(?string $address) : void {$this->address = $address;}
    public function getAddress() : string {return $this->address;}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @param bool $is_organization
     * @return EditableUserCommonDto
     */
    public static function createFromRequest(Request $request, bool $is_organization = false) : EditableUserCommonDto {
        $country = Country::findMe($request->integer('country_id'));
        $family_name = $request->input('family_name');
        $given_names = $request->input('given_names');
        $dto = new static(
            $request->input('email'),
            $is_organization,
            $is_organization ? $request->input('name')
                : User::getPersonName($country, $family_name, $given_names),
            $country->id,
            $request->input('cell_phone'),
        );
        $dto->setAddress($request->input('address'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'is_organization' => $this->is_organization ? 1 : 0,
            'email' => $this->email,
            'country_id' => $this->country_id,
            'cell_phone' => $this->cell_phone,
            'address' => $this->address,
            'name' => $this->name
        ];
    }
}
