<?php

namespace App\DTOs\V1;

use App\Lib\JobType;
use Exception;
use Illuminate\Http\Request;

class VisaEmploymentDto {
    // 속성
    private ?string $org_name;
    private ?string $position_course;
    private ?string $org_address;
    private ?string $org_telephone;

    // Setter, Getter
    public function getJob() : ?JobType {return $this->job;}

    /**
     * @param string|null $name
     * @return void
     * @throws Exception
     */
    public function setOrgName(?string $name) : void {
        if($this->job != JobType::UNEMPLOYED && !$name) throw new Exception('required value');
        $this->org_name = $name;
    }
    public function getOrgName() : ?string {return $this->org_name;}

    /**
     * @param string|null $name
     * @return void
     * @throws Exception
     */
    public function setPositionCourse(?string $name) : void {
        if($this->job != JobType::UNEMPLOYED && !$name) throw new Exception('required value');
        $this->position_course = $name;
    }
    public function getPositionCourse() : ?string {return $this->position_course;}

    /**
     * @param string|null $address
     * @return void
     * @throws Exception
     */
    public function setOrgAddress(?string $address) : void {
        if($this->job != JobType::UNEMPLOYED && !$address) throw new Exception('required value');
        $this->org_address = $address;
    }
    public function getOrgAddress() : ?string {return $this->org_address;}

    /**
     * @param string|null $telephone
     * @return void
     * @throws Exception
     */
    public function setOrgTelephone(?string $telephone) : void {
        if($this->job != JobType::UNEMPLOYED && !$telephone) throw new Exception('required value');
        $this->org_telephone = $telephone;
    }
    public function getOrgTelephone() : ?string {return $this->org_telephone;}

    // Creator
    function __construct(private readonly JobType $job) {}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return VisaEmploymentDto
     * @throws Exception
     */
    public static function createFromRequest(Request $request) : VisaEmploymentDto {
        $dto = new static($request->enum('job', JobType::class));
        $dto->setOrgName($request->input('org_name'));
        $dto->setPositionCourse($request->input('position_course'));
        $dto->setOrgAddress($request->input('org_address'));
        $dto->setOrgTelephone($request->input('org_telephone'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'job' => $this->job->value,
            'org_name' => $this->org_name,
            'position_course' => $this->position_course,
            'org_address' => $this->org_address,
            'org_telephone' => $this->org_telephone
        ];
    }
}
