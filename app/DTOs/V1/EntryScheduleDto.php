<?php

namespace App\DTOs\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EntryScheduleDto {
    // 생성자
    function __construct(
        private readonly Carbon $entry_date,
        private readonly int $entry_limit,
        private readonly Carbon $target_datetime,
        private readonly string $target_place
    ) {}

    // Getter
    public function getEntryDate() : Carbon {return $this->entry_date;}
    public function getEntryLimit() : int {return $this->entry_limit;}
    public function getTargetDatetime() : Carbon {return $this->target_datetime;}
    public function getTargetPlace() : string {return $this->target_place;}

    // Creator

    /**
     * 요청 데이터로부터 DTO 객체를 리턴한다.
     * @param Request $request
     * @return EntryScheduleDto
     */
    public static function createFromRequest(Request $request) : EntryScheduleDto {
        return new static(
            $request->date('entry_date'),
            $request->integer('entry_limit'),
            $request->date('target_datetime'),
            $request->input('target_place')
        );
    }

    // for model
    public function toArray() : array {
        return [
            'entry_date' => $this->entry_date->format('Y-m-d'),
            'entry_limit' => $this->entry_limit,
            'target_datetime' => $this->target_datetime->format('Y-m-d H:i:s'),
            'target_place' => $this->target_place
        ];
    }
}
