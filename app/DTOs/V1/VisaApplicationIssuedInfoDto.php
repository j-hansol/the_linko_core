<?php

namespace App\DTOs\V1;

use App\Models\VisaApplicationIssuedInfo;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VisaApplicationIssuedInfoDto {
    // 생성자
    /**
     * @param string $application_no
     * @param int $application_type
     * @param string $stay_status
     * @param int $stay_period
     * @param Carbon $issue_date
     * @param string $issue_institution
     * @param Carbon $validity_period
     * @throws HttpErrorsException
     */
    function __construct(
        private readonly string $application_no,
        private readonly int $application_type,
        private readonly string $stay_status,
        private readonly int $stay_period,
        private readonly Carbon $issue_date,
        private readonly string $issue_institution,
        private readonly Carbon $validity_period
    ) {
        if(!in_array($this->application_type, [
            VisaApplicationIssuedInfo::TYPE_SINGLE, VisaApplicationIssuedInfo::TYPE_MULTIPLE]))
            throw HttpErrorsException::getInstance([__('errors.visa.invalid_application_type')], 400);
    }

    // Getter
    public function getApplicationNo() : string {return $this->application_no;}
    public function getApplicationType() : int {return $this->application_type;}
    public function getStayStatus() : string {return $this->stay_status;}
    public function getStayPeriod() : int {return $this->stay_period;}
    public function getIssueDate() : Carbon {return $this->issue_date;}
    public function getIssueInstitution() : string {return $this->issue_institution;}
    public function getValidityPeriod() : Carbon {return $this->validity_period;}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return VisaApplicationIssuedInfoDto
     * @throws HttpException
     */
    public static function createFromRequest(Request $request) : VisaApplicationIssuedInfoDto {
        return new static(
            $request->input('application_no'),
            $request->integer('application_type'),
            $request->input('stay_status'),
            $request->integer('stay_period'),
            $request->date('issue_date', 'Y-m-d'),
            $request->input('issue_institution'),
            $request->date('validity_period', 'Y-m-d')
        );
    }

    // for model
    public function toArray() : array {
        return [
            'application_no' => $this->application_no,
            'application_type' => $this->application_type,
            'stay_status' => $this->stay_status,
            'stay_period' => $this->stay_period,
            'issue_date' => $this->issue_date->format('Y-m-d'),
            'issue_institution' => $this->issue_institution,
            'validity_period' => $this->validity_period->format('Y-m-d')
        ];
    }
}
