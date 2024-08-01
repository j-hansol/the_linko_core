<?php

namespace App\Http\Requests\V2;

use App\Lib\EvaluationType;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestEvalItem extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="input_eval_item",
     *     title="평가 설문 내용 입력",
     *     @OA\Property (
     *          property="type",
     *          ref="#/components/schemas/EvaluationType",
     *     ),
     *     @OA\Property (
     *          property="question",
     *          type="string",
     *          description="질문 내용"
     *     ),
     *     @OA\Property (
     *          property="answers",
     *          type="string",
     *          description="응답 내용(아래 예 참조)<br/>5점형 -> 매우 그렇지 않다:1;그렇지 않다:2;보통이다:3;그렇다:4;매우 그렇다:5<br/>선택형 -> 이름1:값1;이름2:값2;... <br/>단문형과 장문형은 입력할 필요가 없습니다."
     *     ),
     *     required={"type","question"}
     * )
     */
    public function rules(): array {
        return [
            'type' => ['required', new Enum(EvaluationType::class)],
            'question' => ['required', 'string'],
            'answers' => ['nullable', 'string']
        ];
    }
}
