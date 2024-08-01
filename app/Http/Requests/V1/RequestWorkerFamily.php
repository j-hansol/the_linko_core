<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestWorkerFamily extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 설정한다.
     * @return array
     * @OA\Schema(
     *     schema="updatable_worker_family",
     *     title="근로자 가족정보 등록 항목",
     *     @OA\Property(
     *          property="country_id",
     *          type="integer",
     *          description="국가(국적)정보 일련번호"
     *     ),
     *     @OA\Property(
     *          property="name",
     *          type="string",
     *          description="이름"
     *     ),
     *     @OA\Property(
     *          property="birthday",
     *          type="string",
     *          format="date",
     *          description="생년월일"
     *     ),
     *     @OA\Property(
     *          property="relationship",
     *          type="string",
     *          description="본인과의 관계"
     *     ),
     *     required={"country_id", "name", "birthday", "relationship"}
     * )
     */
    public function rules(): array {
        return [
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'name' => ['required'],
            'birthday' => ['required', 'date', 'date_format:Y-m-d'],
            'relationship' => ['required']
        ];
    }
}
