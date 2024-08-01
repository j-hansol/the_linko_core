<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestOrderTask extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="input_order_task",
     *     title="업무 요청 내용 입력",
     *     @OA\Property (property="title", type="string", description="업무 요청 제목"),
     *     @OA\Property (property="body", type="string", description="요청내용"),
     *     @OA\Property (property="target_user_id", type="integer", description="업무 수행 대상 계정 일련번호"),
     *     required={"title", "body", "target_user_id"}
     * )
     */
    public function rules(): array {
        return [
            'title' => ['required', 'string'],
            'body' => ['required', 'string'],
            'target_user_id' => ['required', 'integer', 'exists:users,id']
        ];
    }
}
