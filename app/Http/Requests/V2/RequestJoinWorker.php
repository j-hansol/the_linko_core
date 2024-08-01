<?php

namespace App\Http\Requests\V2;

use App\Rules\ExistsValues;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;

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
     *          description="신분증 번호"
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
     *          property="birth_country_id",
     *          type="integer",
     *          description="국가 일련번호"
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
     *     required={"family_name", "given_names", "identity_no", "sex", "birthday", "birth_country_id"}
     * )
     */
    public function rules(): array {
        return [
            'email' => ['required', 'email', 'unique:users,email'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'cell_phone' => ['required'],
            'address' => ['required'],
            'family_name' => ['required'],
            'given_names' => ['required'],
            'identity_no' => ['required'],
            'sex' => ['required', 'in:M,F'],
            'birthday' => ['required', 'date', 'date_format:Y-m-d'],
            'birth_country_id' => ['required', 'integer', 'exists:countries,id'],
            'another_nationality_ids' => ['nullable', new ExistsValues('countries', 'id')],
            'management_org_id' => ['nullable', 'integer', 'exists:users,id'],
            'password' => ['required'],
        ];
    }
}
