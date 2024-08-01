<?php

namespace App\DTOs\V2;

use App\Lib\MemberType;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;

class UserCommonDto {
    private string $timezone = 'UTC';

    // 생성자
    function __construct(
        private readonly string $id_alias,
        private readonly string $email,
        private readonly bool $is_organization,
        private readonly MemberType $type,
        private readonly string $name,
        private readonly int $country_id,
        private readonly string $cell_phone,
        private readonly string $address,

    ) {}

    // Setter, Getter
    public function getIdAlias() : string {return $this->id_alias;}
    public function getEmail() : string {return $this->email;}
    public function getIsOrganization() : bool {return $this->is_organization;}
    public function getType() : MemberType {return $this->type;}
    public function getName() : string {return $this->name;}
    public function getCountryId() : int {return $this->country_id;}
    public function setTimezone(?string $timezone) : void {$this->timezone = $timezone;}
    public function getTimezone() : ?string {return $this->timezone;}
    public function getCellPhone() : string {return $this->cell_phone;}
    public function getAddress() : string {return $this->address;}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @param bool $is_organization
     * @return UserCommonDto
     */
    public static function createFromRequest(Request $request, bool $is_organization = false) : UserCommonDto {
        $country = Country::findMe($request->integer('country_id'));
        $family_name = $request->input('family_name');
        $given_names = $request->input('given_names');
        $dto = new static(
            $request->input('id_alias'),
            $request->input('email'),
            $is_organization,
            $request->enum('type', MemberType::class),
            $is_organization ? $request->input('name')
                : User::getPersonName($country, $family_name, $given_names),
            $country->id,
            $request->input('cell_phone'),
            $request->input('address')
        );
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'is_organization' => $this->is_organization ? 1 : 0,
            'id_alias' => $this->id_alias,
            'email' => $this->email,
            'country_id' => $this->country_id,
            'cell_phone' => $this->cell_phone,
            'address' => $this->address,
            'name' => $this->name
        ];
    }
}
