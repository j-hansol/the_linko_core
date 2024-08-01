<?php

namespace App\Http\Requests\V1;

use App\Rules\CryptDataUnique;
use App\Rules\ExistsValues;
use App\Rules\ValidCryptData;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

class RequestJoinWorker extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="join_worker_profile",
     *     title="회원가입 근로자 프로필",
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
     *          property="another_nationality_ids",
     *          type="array",
     *          @OA\Items(type="integer"),
     *          description="그 외 다른 국적"
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
     *     required={"family_name", "given_names", "sex"}
     * )
     */
    public function rules(): array {
        return [
            'email' => ['nullable', (new ValidCryptData())->type('email'), new CryptDataUnique('users', 'email')],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'cell_phone' => ['nullable', new ValidCryptData()],
            'address' => ['nullable', new ValidCryptData()],
            'family_name' => ['required'],
            'given_names' => ['required'],
            'identity_no' => ['nullable', new ValidCryptData()],
            'sex' => ['required', 'in:M,F'],
            'birthday' => ['nullable', 'date', 'date_format:Y-m-d'],
            'birth_country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'another_nationality_ids' => ['nullable', new ExistsValues('countries', 'id')],
            'management_org_id' => ['nullable', 'integer', 'exists:users,id'],
            'password' => [(new ValidCryptData())->required(true)],
        ];
    }
}
