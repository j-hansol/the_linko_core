<?php

namespace App\Http\Requests\V1;

use App\Rules\ValidCryptData;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestSetFundingDetail extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 설정한다.
     * @return array
     * @OA\Schema(
     *     schema="updatable_visa_funding_detail",
     *     title="비자신청을 위해 등록 가능한 비용지벌 정보 항목",
     *     @OA\Property(
     *          property="travel_costs",
     *          type="number",
     *          format="double",
     *          description="여행 경비"
     *     ),
     *     @OA\Property(
     *          property="payer_name",
     *          type="string",
     *          description="경비지불 기관명 또는 개인 이름"
     *     ),
     *     @OA\Property(
     *          property="payer_relationship",
     *          type="string",
     *          description="본인과의 관계"
     *     ),
     *     @OA\Property(
     *          property="support_type",
     *          type="string",
     *          description="경비 지원 유형"
     *     ),
     *     @OA\Property(
     *          property="payer_contact",
     *          type="string",
     *          description="경비 지불 기관 연락처 (암호화 필요)"
     *     )
     * )
     */
    public function rules(): array {
        return [
            'travel_costs' => ['nullable', 'numeric'],
            'payer_contact' => [(new ValidCryptData())->nullable()]
        ];
    }
}
