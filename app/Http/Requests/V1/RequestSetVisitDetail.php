<?php

namespace App\Http\Requests\V1;

use App\Lib\VisitPurpose;
use App\Rules\RequiredOrNull;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestSetVisitDetail extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 설정한다.
     * @return array
     * @OA\Schema(
     *     schema="updatable_visa_visit_detail",
     *     title="비자신청을 위한 등록 가능한 방문상세 정보 항목",
     *     @OA\Property(
     *          property="purpose",
     *          type="integer",
     *          description="방문 목적 구분",
     *          ref="#/components/schemas/VisitPurpose"
     *     ),
     *     @OA\Property(
     *          property="other_purpose_detail",
     *          type="string",
     *          description="방문 목적이 기타인 경우 기재"
     *     ),
     *     @OA\Property(
     *          property="intended_stay_period",
     *          type="integer",
     *          description="체류기간"
     *     ),
     *     @OA\Property(
     *          property="intended_entry_date",
     *          type="string",
     *          format="date",
     *          description="입국 에정일자"
     *     ),
     *     @OA\Property(
     *          property="address_in_korea",
     *          type="string",
     *          description="국내 거주지 주소"
     *     ),
     *     @OA\Property(
     *          property="contact_in_korea",
     *          type="string",
     *          description="국내 연락처"
     *     ),
     *     required={"purpose", "intended_stay_period", "intended_entry_date", "address_in_korea", "contact_in_korea"}
     * )
     */
    public function rules(): array {
        return [
            'purpose' => ['required', new Enum(VisitPurpose::class)],
            'other_purpose_detail' => [(new RequiredOrNull)->required($this->input('purpose') == VisitPurpose::OTHER->value)],
            'intended_stay_period' => ['required', 'integer', 'min:1'],
            'intended_entry_date' => ['required', 'date', 'date_format:Y-m-d'],
            'address_in_korea' => ['required'],
            'contact_in_korea' => ['required'],
        ];
    }
}
