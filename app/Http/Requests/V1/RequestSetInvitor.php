<?php

namespace App\Http\Requests\V1;

use App\Rules\ValidCryptData;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestSetInvitor extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 설정한다.
     * @return array
     * @OA\Schema(
     *     schema="updatable_visa_invitor",
     *     title="비자신청을 위한 등록가능한 초청자 정보 항목",
     *     @OA\Property(
     *          property="invitor",
     *          type="string",
     *          description="초청자 또는 초청기관 명"
     *     ),
     *     @OA\Property(
     *          property="invitor_relationship",
     *          type="string",
     *          description="본인과의 관계"
     *     ),
     *     @OA\Property(
     *          property="invitor_birthday",
     *          type="string",
     *          format="date",
     *          description="초청자 생년월일"
     *     ),
     *     @OA\Property(
     *          property="invitor_registration_no",
     *          type="string",
     *          description="초청기관 사업자등록 번호"
     *     ),
     *     @OA\Property(
     *          property="invitor_address",
     *          type="string",
     *          description="초청자 또는 초청기관 주소 (암호화 필요)"
     *     ),
     *     @OA\Property(
     *          property="invitor_telephone",
     *          type="string",
     *          description="초청자 또는 초청기관 전화번호 (암호화 필요)"
     *     ),
     *     @OA\Property(
     *          property="invitor_cell_phone",
     *          type="string",
     *          description="초청자 또는 초청기관 휴대전화 번호 (암호화 필요)"
     *     ),
     *     required={"invitor", "invitor_relationship", "invitor_birthday", "invitor_registration_no", "invitor_address", "invitor_telephone", "invitor_cell_phone"}
     * )
     */
    public function rules(): array {
        return [
            'invitor' => ['required'],
            'invitor_relationship' => ['required'],
            'invitor_birthday' => ['required', 'date', 'date_format:Y-m-d'],
            'invitor_registration_no' => ['required'],
            'invitor_address' => [new ValidCryptData()],
            'invitor_telephone' => [new ValidCryptData()],
            'invitor_cell_phone' => [new ValidCryptData()]
        ];
    }
}
