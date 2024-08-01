<?php

namespace App\DTOs\V1;

use App\Lib\EducationDegree;
use Exception;
use Illuminate\Http\Request;

class VisaEducationDto {
    // 속성
    private ?string $other_detail;

    // Setter, Getter
    public function getHighestDegree() : EducationDegree {return $this->highest_degree;}

    /**
     * @param string|null $detail
     * @return void
     * @throws Exception
     */
    public function setOtherDetail(?string $detail) : void {
        if($this->highest_degree == EducationDegree::OTHER && !$detail) throw new Exception('required detail');
        $this->other_detail = $detail;
    }
    public function getOtherDetail() : ?string {return $this->other_detail;}
    public function getSchoolName() : string {return $this->school_name;}
    public function getSchoolLocation() : string {return $this->school_location;}

    // Creator
    function __construct(
        private readonly EducationDegree $highest_degree,
        private readonly string $school_name,
        private readonly string $school_location
    ) {}

    /**
     * 요청 데이러로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return VisaEducationDto
     * @throws Exception
     */
    public static function createFromRequest(Request $request) : VisaEducationDto {
        $dto = new static(
            $request->enum('highest_degree', EducationDegree::class),
            $request->input('school_name'),
            $request->input('school_location')
        );
        $dto->setOtherDetail($request->input('other_detail'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'highest_degree' => $this->highest_degree->value,
            'other_detail' => $this->other_detail,
            'school_name' => $this->school_name,
            'school_location' => $this->school_location,
        ];
    }
}
