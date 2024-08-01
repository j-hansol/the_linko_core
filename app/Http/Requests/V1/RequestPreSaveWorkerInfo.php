<?php

namespace App\Http\Requests\V1;

use App\Rules\CryptDataUnique;
use App\Rules\ValidCryptData;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestPreSaveWorkerInfo extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}


    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="presave_worker_profile",
     *     title="회원가입 근로자 프로필",
     *     @OA\Property (
     *          property="email",
     *          type="string",
     *          description="전자우편 주소 (암호화 필요)"
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
     *          property="hanja_name",
     *          type="string",
     *          description="한자이름"
     *     ),
     *     @OA\Property (
     *          property="identity_no",
     *          type="string",
     *          description="신분증 번호, 암호화 필요"
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
     *          property="old_family_name",
     *          type="string",
     *          description="이전 성"
     *     ),
     *     @OA\Property (
     *          property="old_given_names",
     *          type="string",
     *          description="이전 이름"
     *     ),
     *     @OA\Property (
     *          property="create_account",
     *          type="integer",
     *          enum={"0","1"},
     *          description="계정생성 가능한 경우 생성 (0:생성안함, 1:생성)"
     *     ),
     *     required={"email", "cell_phone", "family_name", "given_names", "sex", "birthday", "create_account"}
     * )
     */
    public function rules(): array {
        return [
            'email' => ['required', (new ValidCryptData())->type('email'), new CryptDataUnique('users', 'email')],
            'cell_phone' => ['required', new ValidCryptData()],
            'address' => ['nullable', new ValidCryptData()],
            'family_name' => ['required'],
            'given_names' => ['required'],
            'identity_no' => ['nullable', new ValidCryptData()],
            'sex' => ['required', 'in:M,F'],
            'birthday' => ['nullable', 'date','date_format:Y-m-d'],
            'create_account' => ['required', 'boolean']
        ];
    }
}
