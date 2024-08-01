<?php

namespace App\DTOs\V1;

use Illuminate\Http\Request;

class WorkerInfoDto {
    // 속성
    private ?string $skills = null;
    private ?string $jobs = null;
    private ?string $hobby = null;
    private ?string $education_part = null;
    private bool $medical_support = false;
    private ?float $height = null;
    private ?float $weight = null;
    private ?string $blood_type = null;
    private ?string $birth_place = null;
    private ?string $civil_status = null;
    private ?string $religion = null;
    private ?string $language = null;
    private ?string $region = null;
    private ?string $current_address = null;
    private ?string $spouse = null;
    private ?string $children_names = null;

    // Setter, Getter
    public function setSkills(?string $skills) : void {$this->skills = $skills;}
    public function getSkills() : ?string {return $this->skills;}
    public function setJob(?string $jobs) : void {$this->jobs = $jobs;}
    public function getJob() : ?string {return $this->jobs;}
    public function setHobby(?string $hobby) : void {$this->hobby = $hobby;}
    public function getHobby() : ?string {return $this->hobby;}
    public function setEducationPart(?string $education_part) : void {$this->education_part = $education_part;}
    public function getEducationPart() : ?string {return $this->education_part;}
    public function setMedicalSupport(?string $medical_support) : void {$this->medical_support = $medical_support;}
    public function getMedicalSupport() : ?string {return $this->medical_support;}
    public function setHeight(?string $height) : void {$this->height = $height;}
    public function getHeight() : ?string {return $this->height;}
    public function setWeight(?string $weight) : void {$this->weight = $weight;}
    public function getWeight() : ?string {return $this->weight;}
    public function setBloodType(?string $blood_type) : void {$this->blood_type = $blood_type;}
    public function getBloodType() : ?string {return $this->blood_type;}
    public function setBirthPlace(?string $place) : void {$this->civil_status = $place;}
    public function getBirthPlace() : ?string {return $this->birth_place;}
    public function setCivilStatus(?string $status) : void {$this->civil_status = $status;}
    public function getCivilStatus() : ?string {return $this->civil_status;}
    public function setReligion(?string $religion) : void {$this->religion = $religion;}
    public function getReligion() : ?string {return $this->religion;}
    public function setLanguage(?string $language) : void {$this->language = $language;}
    public function getLanguage() : ?string {return $this->language;}
    public function setRegion(?string $region) : void {$this->region = $region;}
    public function getRegion() : ?string {return $this->region;}
    public function setCurrentAddress(?string $address) : void {$this->current_address = $address;}
    public function getCurrentAddress() : ?string {return $this->current_address;}
    public function setSpouse(?string $name) : void {$this->spouse = $name;}
    public function getSpouse() : ?string {return $this->spouse;}
    public function setChildrenNames(?string $name) : void {$this->children_names = $name;}
    public function getChildrenNames() : ?string {return $this->children_names;}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return WorkerInfoDto
     */
    public static function createFromRequest(Request $request) : WorkerInfoDto {
        $dto = new static();
        $dto->setSkills($request->input('skills'));
        $dto->setJob($request->input('jobs'));
        $dto->setHobby($request->input('hobby'));
        $dto->setEducationPart($request->input('education_part'));
        $dto->setMedicalSupport($request->boolean('medical_support'));
        $dto->setHeight($request->input('height'));
        $dto->setWeight($request->input('weight'));
        $dto->setBloodType($request->input('blood_type'));
        $dto->setBirthPlace($request->input('birth_place'));
        $dto->setCivilStatus($request->input('civil_status'));
        $dto->setReligion($request->input('religion'));
        $dto->setLanguage($request->input('language'));
        $dto->setRegion($request->input('region'));
        $dto->setCurrentAddress($request->input('current_address'));
        $dto->setSpouse($request->input('spouse'));
        $dto->setChildrenNames($request->input('children_names'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'skills' => $this->skills,
            'jobs' => $this->jobs,
            'hobby' => $this->hobby,
            'education_part' => $this->education_part,
            'medical_support' => $this->medical_support ? 1 : 0,
            'height' => $this->height,
            'weight' => $this->weight,
            'blood_type' => $this->blood_type,
            'birth_place' => $this->birth_place,
            'civil_status' => $this->civil_status,
            'religion' => $this->religion,
            'language' => $this->language,
            'region' => $this->region,
            'current_address' => $this->current_address,
            'spouse' => $this->spouse,
            'children_names' => $this->children_names
        ];
    }
}
