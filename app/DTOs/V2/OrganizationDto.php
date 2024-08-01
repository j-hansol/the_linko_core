<?php

namespace App\DTOs\V2;

use Illuminate\Http\Request;

class OrganizationDto {
    // 속성
    protected ?string $telephone;
    protected ?string $fax;
    protected ?float $longitude;
    protected ?float $latitude;

    // 생성자
    function __construct(
        protected readonly string $registration_no,
        protected readonly string $boss_name,
        protected readonly string $manager_name
    ) {}

    // Setter, Getter
    public function getRegistrationNo() : string {return $this->registration_no;}
    public function getBossName() : string {return $this->boss_name;}
    public function getManagerName() : string {return $this->manager_name;}
    public function setTelephone(?string $no) : void {$this->telephone = $no;}
    public function getTelephone() : ?string {return $this->telephone;}
    public function setFax(?string $no) : void {$this->fax = $no;}
    public function getFax() : ?string {return $this->fax;}
    public function setLongitude(?float $longitude) : void {$this->longitude = $longitude;}
    public function getLongitude() : ?float {return $this->longitude;}
    public function setLatitude(?float $latitude) : void {$this->latitude = $latitude;}
    public function getLatitude() : ?float {return $this->latitude;}

    // Creator

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return OrganizationDto
     */
    static function createFromRequest(Request $request) : OrganizationDto {
        $dto = new static(
            $request->input('registration_no'),
            $request->input('boss_name'),
            $request->input('manager_name'),
        );
        $dto->setTelephone($request->input('telephone'));
        $dto->setFax($request->input('fax'));
        $dto->setLongitude($request->float('longitude'));
        $dto->setLatitude($request->float('latitude'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'registration_no' => $this->registration_no,
            'boss_name' => $this->boss_name,
            'manager_name' => $this->manager_name,
            'telephone' => $this->telephone,
            'fax' => $this->fax,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
        ];
    }
}
