<?php

namespace App\DTOs\V1;


use App\Lib\CryptDataB64 as CryptData;
use Illuminate\Http\Request;

class ContactDto {
    // Getter
    public function getHomeAddress() : string {return $this->home_address;}
    public function getCurrentAddress() : string {return $this->current_address;}
    public function getCellPhone() : string {return $this->cell_phone;}
    public function getEmail() : string {return $this->email;}
    public function getEmergencyFullName() : string {return $this->emergency_full_name;}
    public function getEmergencyCountryId() : int {return $this->emergency_country_id;}
    public function getEmergencyTelephone() : string {return $this->emergency_telephone;}
    public function getEmergencyRelationship() : string {return $this->emergency_relationship;}

    // Creator
    function __construct(
        private readonly string $home_address,
        private readonly string $current_address,
        private readonly string $cell_phone,
        private readonly string $email,
        private readonly string $emergency_full_name,
        private readonly int $emergency_country_id,
        private readonly string $emergency_telephone,
        private readonly string $emergency_relationship
    ) {}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return ContactDto
     */
    public static function createFromRequest(Request $request) : ContactDto {
        return new static(
            CryptData::decrypt($request->input('home_address'), 'home_address'),
            CryptData::decrypt($request->input('current_address'), 'current_address'),
            CryptData::decrypt($request->input('cell_phone'), 'cell_phone'),
            CryptData::decrypt($request->input('email'), 'email'),
            $request->input('emergency_full_name'),
            $request->input('emergency_country_id'),
            CryptData::decrypt($request->input('emergency_telephone'), 'emergency_telephone'),
            $request->input('emergency_relationship'),
        );
    }

    // for model
    public function toArray() : array {
        return [
            'home_address' => $this->home_address,
            'current_address' => $this->current_address,
            'cell_phone' => $this->cell_phone,
            'email' => $this->email,
            'emergency_full_name' => $this->emergency_full_name,
            'emergency_country_id' => $this->emergency_country_id,
            'emergency_telephone' => $this->emergency_telephone,
            'emergency_relationship' => $this->emergency_relationship
        ];
    }
}
