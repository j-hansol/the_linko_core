<?php

namespace App\DTOs\V1;

use App\Lib\CryptDataB64 as CryptData;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvitorDto {
    // Getter
    public function getInvitor() : string {return $this->invitor;}
    public function getInvitorRelationship() : string {return $this->invitor_relationship;}
    public function getInvitorBirthday() : Carbon {return $this->invitor_birthday;}
    public function getInvitorRegistrationNo() : string {return $this->invitor_registration_no;}
    public function getInvitorAddress() : string {return $this->invitor_address;}
    public function getInvitorTelephone() : string {return $this->invitor_telephone;}
    public function getInvitorCellPhone() : string {return $this->invitor_cell_phone;}

    // Creator
    function __construct(
        private readonly string $invitor,
        private readonly string $invitor_relationship,
        private readonly Carbon $invitor_birthday,
        private readonly string $invitor_registration_no,
        private readonly string $invitor_address,
        private readonly string $invitor_telephone,
        private readonly string $invitor_cell_phone
    ) {}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return InvitorDto
     */
    public static function createFromRequest(Request $request) : InvitorDto {
        return new static(
            $request->input('invitor'),
            $request->input('invitor_relationship'),
            Carbon::createFromFormat('Y-m-d', $request->input('invitor_birthday')),
            $request->input('invitor_registration_no'),
            CryptData::decrypt($request->input('invitor_address'), 'invitor_address'),
            CryptData::decrypt($request->input('invitor_telephone'), 'invitor_telephone'),
            CryptData::decrypt($request->input('invitor_cell_phone'), 'invitor_cell_phone')
        );
    }

    // for model
    public function toArray() : array {
        return [
            'invitor' => $this->invitor,
            'invitor_relationship' => $this->invitor_relationship,
            'invitor_birthday' => $this->invitor_birthday->format('Y-m-d'),
            'invitor_registration_no' => $this->invitor_registration_no,
            'invitor_address' => $this->invitor_address,
            'invitor_telephone' => $this->invitor_telephone,
            'invitor_cell_phone' => $this->invitor_cell_phone,
        ];
    }
}
