<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestUpdateUserActiveState extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="active_state",
     *     description="활성화 상태",
     *     @OA\Property (
     *          property="state",
     *          type="integer",
     *          enum={0,1},
     *          description="활성화 여부 (0: 비활성화, 1:활성화)"
     *     ),
     *     required={"state"}
     * )
     */
    public function rules(): array {
        return ['state' => ['required', 'boolean']];
    }
}
