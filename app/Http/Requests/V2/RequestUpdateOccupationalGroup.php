<?php

namespace App\Http\Requests\V2;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestUpdateOccupationalGroup extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * Get the validation rules that apply to the request.
     * @return array
     * @OA\Schema (
     *     schema="editable_occupational_group",
     *     title="수정가능 직업군 정보",
     *     @OA\Property (
     *          property="en_name",
     *          type="string",
     *          description="직업군 이름 (영문)"
     *     ),
     *     @OA\Property (
     *          property="description",
     *          type="string",
     *          description="직업군 설명"
     *     ),
     *     @OA\Property (
     *          property="en_description",
     *          type="string",
     *          description="직업군 설명 (영문)"
     *     ),
     *     @OA\Property (
     *          property="active",
     *          type="integer",
     *          enum={0,1},
     *          description="사용 여부"
     *     ),
     *     @OA\Property (
     *          property="is_education_part",
     *          type="integer",
     *          enum={0,1},
     *          description="교육분야 사용 여부"
     *     )
     * )
     */
    public function rules(): array {
        return [
            'active' => ['required', 'boolean'],
            'is_education_part' => ['required', 'boolean']
        ];
    }
}
