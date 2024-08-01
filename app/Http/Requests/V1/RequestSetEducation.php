<?php

namespace App\Http\Requests\V1;

use App\Lib\EducationDegree;
use App\Rules\RequiredOrNull;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestSetEducation extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 설정한다.
     * @return array
     * @OA\Schema(
     *     schema="updatable_visa_education",
     *     title="비자발급을 위한 등록 가능한 학력정보 항목",
     *     @OA\Property(
     *          property="highest_degree",
     *          type="integer",
     *          description="최종 학력 유형",
     *          ref="#components/schemas/EducationDegree"
     *     ),
     *     @OA\Property(
     *          property="other_detail",
     *          type="string",
     *          description="최종 학력이 기타인 경우 기재"
     *     ),
     *     @OA\Property(
     *          property="school_name",
     *          type="string",
     *          description="학교명"
     *     ),
     *     @OA\Property(
     *          property="school_location",
     *          type="string",
     *          description="학교 소재지"
     *     ),
     *     required={"highest_degree", "school_name", "school_location"}
     * )
     */
    public function rules(): array {
        return [
            'highest_degree' => ['required', new Enum(EducationDegree::class)],
            'other_detail' => [(new RequiredOrNull)->required($this->input('highest_degree') == EducationDegree::OTHER->value)],
            'school_name' => ['required'],
            'school_location' => ['required']
        ];
    }
}
