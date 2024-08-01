<?php

namespace App\Http\Requests\V1;

use App\Rules\CryptDataUnique;
use App\Rules\ValidCryptData;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestJoinManagerOperator extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="join_manager_opwerator",
     *     title="회원가입 관리기관 실무자",
     *     @OA\Property (
     *          property="email",
     *          type="string",
     *          description="이메일 주소 (암호화 필요)"
     *     ),
     *     @OA\Property (
     *          property="family_name",
     *          type="string",
     *          description="성"
     *     ),
     *     @OA\Property (
     *          property="given_names",
     *          type="string",
     *          description="이름"
     *     ),
     *     @OA\Property (
     *          property="cell_phone",
     *          type="string",
     *          description="휴대전화 번호 (암호화 필요)"
     *     ),
     *     @OA\Property (
     *          property="address",
     *          type="string",
     *          description="주소 (암호화 필요)"
     *     ),
     *     @OA\Property (
     *          property="sex",
     *          type="string",
     *          enum={"M","F"},
     *          description="성별"
     *     ),
     *     @OA\Property (
     *          property="birthday",
     *          type="date",
     *          description="생년월일"
     *     ),
     *     @OA\Property (
     *           property="password",
     *           type="string",
     *           description="비밀번호 (암호화 필요)"
     *      ),
     *     required={"email", "family_name", "given_names", "cell_phone", "sex", "birthday0", "password"}
     * )
     */
    public function rules(): array {
        return [
            'email' => ['required', (new ValidCryptData())->type('email'), new CryptDataUnique('users', 'email')],
            'cell_phone' => ['required', new ValidCryptData()],
            'address' => ['required', new ValidCryptData()],
            'family_name' => ['required'],
            'given_names' => ['required'],
            'sex' => ['required', 'in:M,F'],
            'birthday' => ['required', 'date', 'date_format:Y-m-d'],
            'password' => ['required', new ValidCryptData()],
        ];
    }
}
