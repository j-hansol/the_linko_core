<?php

namespace App\DTOs\V2;

use Illuminate\Http\Request;

class VisaDocumentTypeDto {
    // 속성
    private ?string $description;
    private ?string $en_description;

    // 생성자
    function __construct(
        private readonly string $name,
        private readonly string $en_name,
        private readonly bool $active
    ) {}

    // Setter, Getter
    public function getName() : string {return $this->name;}
    public function getEnName() : string {return $this->en_name;}
    public function getActive() : bool {return $this->active;}
    public function setDescription(?string $description) : void {$this->description = $description;}
    public function getDescription() : string {return $this->description;}
    public function setEnDescription(?string $description) : void {$this->en_description = $description;}
    public function getEnDescription() : ?string {return $this->en_description;}

    // Creator

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return VisaDocumentTypeDto
     */
    public static function createFromRequest(Request $request) : VisaDocumentTypeDto {
        $dto = new static(
            $request->input('name'),
            $request->input('en_name'),
            $request->boolean('active')
        );
        $dto->setDescription($request->input('description'));
        $dto->setEnDescription($request->input('en_description'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'name' => $this->name,
            'en_name' => $this->en_name,
            'description' => $this->description,
            'en_description' => $this->en_description,
            'active' => $this->active ? 1 : 0
        ];
    }
}
