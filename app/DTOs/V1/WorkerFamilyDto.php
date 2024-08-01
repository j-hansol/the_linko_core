<?php

namespace App\DTOs\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WorkerFamilyDto {
    // Getter
    public function getCountryId() : int {return  $this->country_id;}
    public function getName() : string {return $this->name;}
    public function getBirthday() : Carbon {return $this->birthday;}
    public function getRelationship() : string {return $this->relationship;}

    // Creator
    function __construct(
        private readonly int $country_id,
        private readonly string $name,
        private readonly Carbon $birthday,
        private readonly string $relationship
    ) {}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return WorkerFamilyDto
     */
    public static function createFromRequest(Request $request) : WorkerFamilyDto {
        return new static(
            $request->integer('country_id'),
            $request->input('name'),
            $request->date('birthday', 'Y-m-d'),
            $request->input('relationship')
        );
    }

    // for model
    public function toArray() : array {
        return [
            'country_id' => $this->country_id,
            'name' => $this->name,
            'birthday' => $this->birthday->format('Y-m-d'),
            'relationship' => $this->relationship
        ];
    }
}
