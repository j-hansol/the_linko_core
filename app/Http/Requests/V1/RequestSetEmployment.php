<?php

namespace App\Http\Requests\V1;

use App\Lib\JobType;
use App\Rules\RequiredOrNull;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestSetEmployment extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 설정한다.
     * @return array[]
     * @OA\Schema(
     *     schema="updatable_visa_employment",
     *     title="비자신청을 위한 등록 가능한 직업정보 항목",
     *     @OA\Property(
     *          property="job",
     *          type="integer",
     *          description="직업구분",
     *          ref="#/components/schemas/JobType",
     *     ),
     *     @OA\Property(
     *          property="org_name",
     *          type="string",
     *          description="상호/학교명 (무직의 경우 공백 가능)"
     *     ),
     *     @OA\Property(
     *          property="position_course",
     *          type="string",
     *          description="직위/과정 (무직의 경우 공백 가능)"
     *     ),
     *     @OA\Property(
     *          property="org_address",
     *          type="string",
     *          description="상호/확교 소재지 주소 (무직의 경우 공백 가능)"
     *     ),
     *     @OA\Property(
     *          property="org_telephone",
     *          type="string",
     *          description="상호/학교 전화번호 (무직의 경우 공백 가능)"
     *     ),
     *     required={"job"}
     * )
     */
    public function rules(): array {
        return [
            'job' => ['required', 'integer', new Enum(JobType::class)],
            'org_name' => [(new RequiredOrNull)->required($this->input('job') != JobType::UNEMPLOYED->value)],
            'position_course' => [(new RequiredOrNull)->required($this->input('job') != JobType::UNEMPLOYED->value)],
            'org_address'  => [(new RequiredOrNull)->required($this->input('job') != JobType::UNEMPLOYED->value)],
            'org_telephone'  => [(new RequiredOrNull)->required($this->input('job') != JobType::UNEMPLOYED->value)]
        ];
    }
}
