<?php

namespace App\DTOs\V1;

use App\Lib\PassportType;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class PassportDto {
    // 속성
    private ?string $other_type_detail;
    private ?string $other_passport_detail;
    private ?PassportType $other_passport_type;
    private ?string $other_passport_no;
    private ?int $other_passport_country_id;
    private ?Carbon $other_passport_expire_date;

    // Setter, Getter
    public function getPassportType() : PassportType {return $this->passport_type;}

    /**
     * @param string|null $detail
     * @return void
     * @throws Exception
     */
    public function setOtherTypeDetail(?string $detail) : void {
        if($this->getPassportType() == PassportType::TYPE_OTHER && !$detail)
            throw new Exception('required other type detail');
        $this->other_type_detail = $detail;
    }
    public function getOtherTypeDetail() : ?string {return $this->other_type_detail;}
    public function getPassportNo() : string {return $this->passport_no;}
    public function getPassportCountryId() : int {return $this->passport_country_id;}
    public function getIssuePlace() : string {return $this->issue_place;}
    public function getIssueDate() : Carbon {return $this->issue_date;}
    public function getExpireDate() : Carbon {return $this->expire_date;}
    public function getOtherPassport() : int {return $this->other_passport;}

    /**
     * @param string|null $detail
     * @return void
     * @throws Exception
     */
    public function setOtherPassportDetail(?string $detail) : void {
        if($this->other_passport == 1 && !$detail) throw new Exception('required other passport info ');
        $this->other_passport_detail = $detail;
    }
    public function getOtherPassportDetail() : ?string {return $this->other_passport_detail;}

    /**
     * @param int|null $type
     * @return void
     * @throws Exception
     */
    public function setOtherPassportType(?PassportType $type) : void {
        if($this->other_passport == 1 && !$type) throw new Exception('required other passport info ');
        $this->other_passport_type = $type;
    }
    public function getOtherPassportType() : ?PassportType {return $this->other_passport_type;}

    /**
     * @param string|null $no
     * @return void
     * @throws Exception
     */
    public function setOtherPassportNo(?string $no) : void {
        if($this->other_passport == 1 && !$no) throw new Exception('required other passport info ');
        $this->other_passport_no = $no;
    }
    public function getOtherPassportNo() : ?string {return $this->other_passport_no;}

    /**
     * @param int|null $id
     * @return void
     * @throws Exception
     */
    public function setOtherPassportCountryId(?int $id) : void {
        if($this->other_passport == 1 && !$id) throw new Exception('required other passport info ');
        $this->other_passport_country_id = $id;
    }
    public function getOtherPassportCountryId() : ?int {return $this->other_passport_country_id;}

    /**
     * @param Carbon|null $date
     * @return void
     * @throws Exception
     */
    public function setOtherPassportExpireDate(?Carbon $date) : void {
        if($this->other_passport == 1 && !$date) throw new Exception('required other passport expire date');
        $this->other_passport_expire_date = $date;
    }
    public function getOtherPassportExpireDate() : ?Carbon {return $this->other_passport_expire_date;}

    // Creator
    function __construct(
        private readonly PassportType $passport_type,
        private readonly string $passport_no,
        private readonly int $passport_country_id,
        private readonly string $issue_place,
        private readonly Carbon $issue_date,
        private readonly Carbon $expire_date,
        private readonly int $other_passport
    ) {}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return PassportDto
     * @throws Exception
     */
    public static function createFromRequest(Request $request) : PassportDto {
        $dto = new static(
            $request->enum('passport_type', PassportType::class),
            $request->input('passport_no'),
            $request->input('passport_country_id'),
            $request->input('issue_place'),
            Carbon::createFromFormat('Y-m-d', $request->input('issue_date')),
            Carbon::createFromFormat('Y-m-d', $request->input('expire_date')),
            $request->boolean('other_passport')
        );
        $dto->setOtherTypeDetail($request->input('other_type_detail'));
        $dto->setOtherPassportDetail($request->input('other_passport_detail'));
        $dto->setOtherPassportType($request->enum('other_passport_type', PassportType::class));
        $dto->setOtherPassportNo($request->input('other_passport_no'));
        $dto->setOtherPassportCountryId($request->input('other_passport_country_id'));
        $dto->setOtherPassportExpireDate($request->input('other_passport_expire_date') ?
            Carbon::createFromFormat('Y-m-d', $request->input('other_passport_expire_date')) : null);
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'passport_type' => $this->passport_type->value,
            'other_type_detail' => $this->other_type_detail,
            'passport_no' => $this->passport_no,
            'passport_country_id' => $this->passport_country_id,
            'issue_place' => $this->issue_place,
            'issue_date' => $this->issue_date->format('Y-m-d'),
            'expire_date' => $this->expire_date->format('Y-m-d'),
            'other_passport' => $this->other_passport,
            'other_passport_detail' => $this->other_passport_detail,
            'other_passport_type' => $this->other_passport_type?->value,
            'other_passport_no' => $this->other_passport_no,
            'other_passport_country_id' => $this->other_passport_country_id,
            'other_passport_expire_date' => $this->other_passport_expire_date
        ];
    }
}
