<?php

namespace App\DTOs\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EntryInfoDto {
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

    public static function createFromRequest(Request $request) : ?EntryInfoDto {

    }
}

