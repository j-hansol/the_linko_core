<?php

namespace App\Http\Requests\V1;

use App\Rules\ValidCryptData;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestSetAssistance extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 설정한다.
     * @return array
     * @OA\Schema(
     *     schema="updatable_visa_assitance",
     *     title="바자신청을 위해 등록 가능한 서류작성 도우미 정보 항목",
     *     @OA\Property(
     *          property="consulting_user_id",
     *          type="integer",
     *          description="행정사의 컨설팅의 경우 행정사 계정 일련본호 기재"
     *     ),
     *     @OA\Property(
     *          property="assistant_name",
     *          type="string",
     *          description="도우미 이름"
     *     ),
     *     @OA\Property(
     *          property="assistant_birthday",
     *          type="string",
     *          description="도우미 생년월일"
     *     ),
     *     @OA\Property(
     *          property="assistant_telephone",
     *          type="string",
     *          description="도우미 전화번호 (암호화 필요)"
     *     ),
     *     @OA\Property(
     *          property="assistant_relationship",
     *          type="string",
     *          description="본인과의 관계"
     *     ),
     *     required={"assistant_name", "assistant_birthday", "assistant_telephone", "assistant_relationship"}
     * )
     */
    public function rules(): array {
        return [
            'consulting_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'assistant_name' => ['required'],
            'assistant_birthday' => ['required'],
            'assistant_telephone' => [new ValidCryptData()],
            'assistant_relationship' => ['required']
        ];
    }
}
