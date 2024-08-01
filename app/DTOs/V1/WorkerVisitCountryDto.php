<?php

namespace App\DTOs\V1;


use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WorkerVisitCountryDto {
    // Getter
    public function getCountryId() : int {return $this->country_id;}
    public function getVisitPurpose() : string {return $this->visit_purpose;}
    public function getEntryDate() : Carbon {return $this->entry_date;}
    public function getDepartureDate() : Carbon {return $this->departure_date;}

    // Creator
    function __construct(
        private readonly int $country_id,
        private readonly string $visit_purpose,
        private readonly Carbon $entry_date,
        private readonly Carbon $departure_date
    ) {}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return WorkerVisitCountryDto
     */
    public static function createFromRequest(Request $request) : WorkerVisitCountryDto {
        return new static(
            $request->integer('country_id'),
            $request->input('visit_purpose'),
            $request->date('entry_date', 'Y-m-d'),
            $request->date('departure_date', 'Y-m-d')
        );
    }

    // for model
    public function toArray() : array {
        return [
            'country_id' => $this->country_id,
            'visit_purpose' => $this->visit_purpose,
            'entry_date' => $this->entry_date->format('Y-m-d'),
            'departure_date' => $this->departure_date->format('Y-m-d')
        ];
    }
}
