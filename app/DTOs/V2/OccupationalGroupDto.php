<?php

namespace App\DTOs\V2;

use Illuminate\Http\Request;

class OccupationalGroupDto {
    // 속성
    private ?string $en_name;
    private ?string $description;
    private ?string $en_description;

    // 생성자
    function __construct(
        private readonly bool $active,
        private readonly bool $is_education_part
    ) {}

    // Setter, Getter
    public function getActive() : bool {return $this->active;}
    public function getIsEducationPart() : bool {return $this->is_education_part;}
    public function setEnName(?string $name) : void {$this->en_name = $name;}
    public function getEnName() : ?string {return $this->en_name;}
    public function setDescription(?string $description) : void {$this->description = $description;}
    public function getDescription() : ?string {return $this->description;}
    public function setEnDescription(?string $description) : void {$this->en_description = $description;}
    public function getEnDescription() : ?string {return $this->en_description;}

    // Creator
    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return OccupationalGroupDto
     */
    public static function createFromRequest(Request $request) : OccupationalGroupDto {
        $dto = new static(
            $request->boolean('active'),
            $request->boolean('is_education_part')
        );
        $dto->setEnName($request->input('en_name'));
        $dto->setDescription($request->input('description'));
        $dto->setEnDescription($request->input('en_description'));
        return $dto;
    }

    // for Model
    public function toArray() : array {
        return [
            'en_name' => $this->en_name,
            'description' => $this->description,
            'en_description' => $this->en_description,
            'active' => $this->active ? 1 : 0,
            'is_education_part' => $this->is_education_part ? 1 : 0,
        ];
    }
}
