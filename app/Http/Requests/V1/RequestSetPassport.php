<?php

namespace App\Http\Requests\V1;

use App\Lib\PassportType;
use App\Rules\RequiredOrNull;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestSetPassport extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 설정한다.
     * @return array
     * @OA\Schema(
     *     schema="updatable_visa_passport",
     *     title="비자신청용 여권 등록 항목",
     *     @OA\Property(
     *          property="passport_type",
     *          type="integer",
     *          description="여권종류",
     *          ref="#/components/schemas/PassportType"
     *     ),
     *     @OA\Property(
     *          property="other_type_detail",
     *          type="string",
     *          description="여권 종류가 기타인 경우 기재"
     *     ),
     *     @OA\Property(
     *          property="passport_no",
     *          type="string",
     *          description="여권번호"
     *     ),
     *     @OA\Property(
     *          property="passport_country_id",
     *          type="integer",
     *          description="발급국가",
     *     ),
     *     @OA\Property(
     *          property="issue_place",
     *          type="string",
     *          description="발급지"
     *     ),
     *     @OA\Property(
     *          property="issue_date",
     *          type="string",
     *          format="date",
     *          description="발급일자"
     *     ),
     *     @OA\Property(
     *          property="expire_date",
     *          type="string",
     *          format="date",
     *          description="만료일자"
     *     ),
     *     @OA\Property(
     *          property="other_passport",
     *          type="integer",
     *          enum={"0","1"},
     *          description="다른 여권 소지 여부"
     *     ),
     *     @OA\Property(
     *          property="other_passport_detail",
     *          type="string",
     *          description="다른 여권 소지의 경우 기재"
     *     ),
     *     @OA\Property(
     *          property="other_passport_type",
     *          type="integer",
     *          description="소지중인 다른 여권 종류",
     *          ref="#/components/schemas/PassportType"
     *     ),
     *     @OA\Property(
     *          property="other_passport_no",
     *          type="string",
     *          description="소지징인 다른 여권 번호"
     *     ),
     *     @OA\Property(
     *          property="other_passport_country_id",
     *          type="integer",
     *          description="소지징인 다른 여권 발급국가",
     *     ),
     *     @OA\Property(
     *          property="other_passport_expire_date",
     *          type="string",
     *          format="date",
     *          description="소지징인 다른 여권 만료일자"
     *     ),
     *     required={"passport_type", "passport_no", "passport_country_id", "issue_place", "issue_date", "expire_date", "other_passport"}
     * )
     */
    public function rules(): array {
        return [
            'passport_type' => ['required', new Enum(PassportType::class)],
            'other_type_detail' => ['required_if:passport_type,' . PassportType::TYPE_OTHER->value],
            'passport_no' => ['required'],
            'passport_country_id' => ['required', 'integer', 'exists:countries,id'],
            'issue_place' => ['required'],
            'issue_date' => ['required', 'date', 'date_format:Y-m-d'],
            'expire_date' => ['required', 'date', 'date_format:Y-m-d'],
            'other_passport' => ['required', 'boolean'],
            'other_passport_detail' => [(new RequiredOrNull)->required($this->input('other_passport') == 1)],
            'other_passport_type' => [(new RequiredOrNull)->required($this->input('other_passport') == 1)],
            'other_passport_no' => [(new RequiredOrNull)->required($this->input('other_passport') == 1)],
            'other_passport_country_id' => [(new RequiredOrNull)->required($this->input('other_passport') == 1), 'exists:countries,id'],
            'other_passport_expire_date' => [(new RequiredOrNull)->required($this->input('other_passport') == 1)]
        ];
    }
}
