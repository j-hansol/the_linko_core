<?php

namespace App\DTOs\V1;

use App\Models\Country;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WorkerPassportDto implements IWorkerPassportDto {
    private array $field = [
        'passport_no', 'passport_country_id', 'country_iso3_code', 'country_name', 'family_name', 'middle_name',
        'given_names', 'birthday', 'birth_place', 'sex', 'issue_place', 'issue_date', 'expire_date'
    ];
    // 속성
    private ?Country $country;
    private ?string $middle_name = null;
    private ?int $country_id = null;
    private ?string $country_iso3_code = null;
    private ?string $country_name = null;
    private ?string $sex = 'M';
    private ?string $nationality = null;
    private ?string $birth_place = null;

    // 생성자

    /**
     * @param string $passport_no
     * @param string $family_name
     * @param string $given_names
     * @param Carbon $birthday
     * @param string $issue_place
     * @param Carbon $issue_date
     * @param Carbon $expire_date
     * @param string $sex
     * @param Country $country
     * @throws HttpErrorsException
     */
    function __construct(
        private readonly string $passport_no,
        private readonly string $family_name,
        private readonly string $given_names,
        private readonly Carbon $birthday,
        private readonly string $issue_place,
        private readonly Carbon $issue_date,
        private readonly Carbon $expire_date,
        string $sex,
        Country $country
    ) {
        if(!in_array($sex, ['M', "F"]))
            throw HttpErrorsException::getInstance([__('errors.user.invalid_gender')], 400);
        $this->sex = $sex;
        $this->country_id = $country->id;
        $this->country_name = $country->en_name;
        $this->country_iso3_code = $country->iso3_code;
    }

    // Setter, Getter
    public function getPassportNo() : string {return $this->passport_no;}
    public function getFamilyName() : string {return $this->family_name;}
    public function getGivenNames() : string {return $this->given_names;}
    public function getBirthday() : Carbon {return $this->birthday;}
    public function setBirthPlace(?string $place) : void {$this->birth_place = $place;}
    public function getBirthPlace() : ?string {return $this->birth_place;}
    public function getIssuePlace() : string {return $this->issue_place;}
    public function getIssueDate() : Carbon {return $this->issue_date;}
    public function getExpireDate() : Carbon {return $this->expire_date;}
    public function getSex() : string {return $this->sex;}
    public function getCountry() : ?Country {return Country::findMe($this->country_id);}
    public function getCountryId() : int {return $this->country_id;}
    public function getCountryIso3Code() : string {return $this->country_iso3_code;}
    public function getCountryName() : string {return $this->country_name;}
    public function setNationality(?string $nationality) : void {$this->nationality = $nationality;}
    public function getNationality() : ?string {return $this->nationality;}
    public function setMiddleName(?string $name) : void {$this->middle_name = $name;}
    public function getMiddleName() : ?string {return $this->middle_name;}

    // Creator
    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return WorkerPassportDto
     * @throws HttpException
     */
    public static function createFromRequest(Request $request) : WorkerPassportDto {
        $dto = new static(
            $request->input('passport_no'),
            $request->input('family_name'),
            $request->input('given_names'),
            $request->date('birthday'),
            $request->input('issue_place'),
            $request->date('issue_date'),
            $request->date('expire_date'),
            $request->input('sex'),
            Country::findMe($request->integer('passport_country_id'))
        );

        $dto->setMiddleName($request->input('middle_name'));
        $dto->setNationality($request->input('nationality'));
        $dto->setBirthPlace($request->input('birth_place'));
        return $dto;
    }

    // for Model
    public function toArray() : array {
        return [
            'passport_no' => $this->passport_no,
            'passport_country_id' => $this->country_id,
            'country_iso3_code' => $this->country_iso3_code,
            'country_name' => $this->country_name,
            'nationality' => $this->nationality,
            'family_name' => $this->family_name,
            'middle_name' => $this->middle_name,
            'given_names' => $this->given_names,
            'birthday' => $this->birthday->format('Y-m-d'),
            'birth_place' => $this->birth_place,
            'sex' => $this->sex,
            'issue_place' => $this->issue_place,
            'issue_date' => $this->issue_date->format('Y-m-d'),
            'expire_date' => $this->expire_date->format('Y-m-d'),
        ];
    }
}
