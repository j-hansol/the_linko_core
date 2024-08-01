<?php

namespace App\DTOs\V1;

use App\Lib\VisitPurpose;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VisitDetailDto {
    // 속성
    private ?string $other_purpose_detail;
    private ?string $visit_korea_ids;
    private ?string $visit_country_ids;
    private ?string $stay_family_ids;
    private ?string $family_member_ids;

    // Setter, Getter
    public function getPurpose() : VisitPurpose {return $this->purpose;}
    public function setOtherPurposeDetail(?string $detail) : void {$this->other_purpose_detail = $detail;}
    public function getOtherPurposeDetail() : ?string {return $this->other_purpose_detail;}
    public function getIntendedStayPeriod() : int {return $this->intended_stay_period;}
    public function getIntendedEntryDate() : Carbon {return $this->intended_entry_date;}
    public function getAddressInKorea() : string {return $this->address_in_korea;}
    public function getContactInKorea() : string {return $this->contact_in_korea;}

    // Creator
    function __construct(
        private readonly VisitPurpose $purpose,
        private readonly int $intended_stay_period,
        private readonly Carbon $intended_entry_date,
        private readonly string $address_in_korea,
        private readonly string $contact_in_korea
    ) {}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return VisitDetailDto
     */
    public static function createFromRequest(Request $request) : VisitDetailDto {
        $dto = new static(
            $request->enum('purpose', VisitPurpose::class),
            $request->integer('intended_stay_period'),
            Carbon::createFromFormat('Y-m-d', $request->input('intended_entry_date')),
            $request->input('address_in_korea'),
            $request->input('contact_in_korea')
        );
        $dto->setOtherPurposeDetail($request->input('other_purpose_detail'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'purpose' =>$this->purpose->value,
            'other_purpose_detail' => $this->other_purpose_detail,
            'intended_stay_period' => $this->intended_stay_period,
            'intended_entry_date' => $this->intended_entry_date,
            'address_in_korea' => $this->address_in_korea,
            'contact_in_korea' => $this->contact_in_korea,
        ];
    }
}
