<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestTask extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="input_task",
     *     title="기업 업무 정보 입력",
     *     @OA\Property (
     *         property="name",
     *         type="string",
     *         description="업무 이름"
     *     ),
     *     @OA\Property (
     *         property="en_name",
     *         type="string",
     *         description="업무 영문 이름"
     *     ),
     *     @OA\Property (
     *         property="description",
     *         type="string",
     *         description="업무 설명"
     *     ),
     *     @OA\Property (
     *         property="en_description",
     *         type="string",
     *         description="업무 영문 설명"
     *     ),
     *     @OA\Property (
     *         property="mobie",
     *         type="string",
     *         format="binary",
     *         description="동영상파일"
     *     ),
     *     @OA\Property (
     *         property="delete_priv_movie",
     *         type="integer",
     *         enum={"0","1"},
     *         description="이전 영상 삭제 여부 (0:유지, 1:삭제) 기본값 유지, 유지의 경우 새로운 영상 파일은 무시됨"
     *     ),
     *     required={"name","en_name"}
     * )
     */
    public function rules(): array {
        return [
            'name' => ['required', 'string'],
            'en_name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'en_description' => ['nullable', 'string'],
            'movie' => ['nullable', 'file', 'mimes:video/mp4', 'max:31457280'],
            'delete_prev_movie' => ['nullable', 'boolean']
        ];
    }
}
