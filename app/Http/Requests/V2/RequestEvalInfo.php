<?php

namespace App\Http\Requests\V2;

use App\Lib\EvalTarget;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestEvalInfo extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 평가 마스터 정보 유효성을 검사한다.
     * @return array
     * @OA\Schema(
     *     schema="eval_info",
     *     title="평가정보 입력",
     *     @OA\Property(
     *          property="title",
     *          type="string",
     *          description="평가정보 제목"
     *     ),
     *     @OA\Property(
     *          property="target",
     *          type="integer",
     *          enum={"10","20"},
     *          description="평가 타겟"
     *     ),
     *     @OA\Property(
     *          property="description",
     *          type="string",
     *          description="설명"
     *     ),
     *     @OA\Property(
     *          property="active",
     *          type="integer",
     *          enum={"0","1"},
     *          description="활성화 여부"
     *     ),
     *     required={"title","target","active"}
     * )
     */
    public function rules(): array {
        return [
            'title' => ['required', 'string'],
            'target' => ['required', new Enum(EvalTarget::class)],
            'description' => ['nullable', 'string'],
            'active' => ['required', 'in:0,1']
        ];
    }
}
