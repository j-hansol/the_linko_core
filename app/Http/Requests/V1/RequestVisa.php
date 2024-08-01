<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestVisa extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 설정한다.
     * @return array
     * @OA\Schema(
     *     schema="request_visa",
     *     title="비자발급 신청",
     *     @OA\Property(
     *          property="order_stay_period",
     *          type="integer",
     *          enum={10,20},
     *          description="체류기간 구분"
     *     ),
     *     @OA\Property(
     *          property="order_stay_status",
     *          type="string",
     *          description="체류자격"
     *     )
     * )
     */
    public function rules(): array {
        return [
            'order_stay_period' => ['required', 'integer', 'in:10,20'],
            'order_stay_status' => ['required']
        ];
    }
}
