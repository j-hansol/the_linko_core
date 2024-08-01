<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestSetSubContract extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 설정한다.
     * @return array
     * @OA\Schema(
     *     schema="add_sub_contract",
     *     title="하위 계약 내용 추가",
     *     @OA\Property(
     *          property="sub_recipient_user_id",
     *          type="integer",
     *          description="하위 계약 수주자"
     *     ),
     *     @OA\Property(
     *          property="sub_title",
     *          type="string",
     *          description="하위 계약 제목"
     *     ),
     *     @OA\Property(
     *          property="sub_body",
     *          type="string",
     *          description="하위 계약 내용"
     *     ),
     *     @OA\Property(
     *          property="sub_contract_date",
     *          type="string",
     *          format="date",
     *          description="하위 계약 날짜"
     *     )
     * )
     */
    public function rules(): array {
        return [
            'sub_recipient_user_id' => ['required', 'integer', 'exists:useres,id'],
            'sub_title' => ['required', 'string'],
            'sub_body' => ['required', 'string'],
            'sub_contract_date' => ['nullable', 'date', 'date_format:Y-m-d']
        ];
    }
}
