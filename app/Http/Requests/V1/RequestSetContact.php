<?php

namespace App\Http\Requests\V1;

use App\Rules\ValidCryptData;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestSetContact extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 설정한다.
     * @return array
     * @OA\Schema(
     *     schema="updatable_visa_contact",
     *     title="비자신청 시 등록 가능한 연락처 항목",
     *     @OA\Property(
     *          property="home_address",
     *          type="string",
     *          description="비자신청 기준(전산동록 상) 주소 (암호화 필요)"
     *     ),
     *     @OA\Property(
     *          property="current_address",
     *          type="string",
     *          description="현 거주지 주소 (암호화 필요)"
     *     ),
     *     @OA\Property(
     *          property="cell_phone",
     *          type="string",
     *          description="휴대전화 번호 (암호화 필요)"
     *     ),
     *     @OA\Property(
     *           property="email",
     *           type="string",
     *           description="전자우편 주소 (암호화 필요)"
     *      ),
     *     @OA\Property(
     *          property="emergency_full_name",
     *          type="string",
     *          description="비상 연락처 이름"
     *     ),
     *     @OA\Property(
     *          property="emergency_country_id",
     *          type="integer",
     *          description="비상연락 국가",
     *     ),
     *     @OA\Property(
     *          property="emergency_telephone",
     *          type="string",
     *          description="비상연락 전화번호 (암호화 필요)"
     *     ),
     *     @OA\Property(
     *          property="emergency_relationship",
     *          type="string",
     *          description="비상연락처와의 관계"
     *     ),
     *     required={"home_address", "current_address", "cell_phone", "email", "emergency_full_name", "emergency_country_id", "emergency_telephone", "emergency_relationship"}
     * )
     */
    public function rules(): array {
        return [
            'home_address' => [(new ValidCryptData())->required(true)],
            'current_address' => [(new ValidCryptData())->required(true)],
            'cell_phone' => [(new ValidCryptData())->required(true)],
            'email' => [(new ValidCryptData())->type('email')->required(true)],
            'emergency_full_name' => ['required'],
            'emergency_country_id' => ['required', 'integer', 'exists:countries,id'],
            'emergency_telephone' => [(new ValidCryptData())->required(true)],
            'emergency_relationship' => ['required']
        ];
    }
}
