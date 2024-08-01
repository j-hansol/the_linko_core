<?php

namespace App\Http\Requests\V1;

use App\Rules\ExistsValues;
use App\Rules\ValidCryptData;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestUpdateProfile extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 비자발급시 필요한 프로필 정보 변경을 위한 유효성 검사 규칙을 리턴한다.
     * @return array
     * @see RequestJoinWorker
     * @OA\Schema (
     *     schema="update_visa_profile",
     *     title="비자 신청을 위 ",
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
     *          property="country_id",
     *          type="integer",
     *          description="국가(국적) 일련번호"
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
     *     required={"family_name", "given_names", "identity_no", "sex", "birthday", "country_id", "birth_country_id"}
     *  )
     */
    public function rules(): array {
        return [
            'family_name' => ['required'],
            'given_names' => ['required'],
            'hanja_name' => ['nullable'],
            'identity_no' => [(new ValidCryptData())->required(true)],
            'sex' => ['required', 'in:M,F'],
            'birthday' => ['required', 'date', 'date_format:Y-m-d'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'birth_country_id' => ['required', 'integer', 'exists:countries,id'],
            'another_nationality_ids' => [(new ExistsValues('countries', 'id'))->nullable(true)],
        ];
    }
}
