<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestWorkerPassport extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="input_worker_passport",
     *     title="근로자 여권정보 입력",
     *     @OA\Property (property="passport_no", type="string", description="여권 번호"),
     *     @OA\Property (property="passport_country_id", type="integer", description="발급 국가정보 일련번호"),
     *     @OA\Property (property="nationality", type="string", description="국적"),
     *     @OA\Property (property="family_name", type="string", description="이름(성)"),
     *     @OA\Property (property="middle_name", type="string", description="중간이름"),
     *     @OA\Property (property="given_names", type="string", description="이름"),
     *     @OA\Property (property="birthday", type="string", format="date", description="생년월일"),
     *     @OA\Property (property="birth_place", type="string", description="출생지"),
     *     @OA\Property (property="sex", type="string", enum={"M","F"}, description="성별"),
     *     @OA\Property (property="issue_place", type="string", description="발급지"),
     *     @OA\Property (property="issue_date", type="string", format="date", description="발급일"),
     *     @OA\Property (property="expire_date", type="string", format="date", description="만료일"),
     *     required={"passport_no","passport_country_id","family_name","given_names","birthday","sex","issue_place","issue_date","expire_date"}
     * )
     */
    public function rules(): array {
        return [
            'passport_no' => ['required'],
            'passport_country_id' => ['required', 'integer', 'exists:countries,id'],
            'family_name' => ['required'],
            'given_names' => ['required'],
            'birthday' => ['required', 'date', 'date_format:Y-m-d'],
            'birth_place' => ['nullable'],
            'sex' => ['required', 'in:M,F'],
            'issue_place' => ['required'],
            'issue_date' => ['required', 'date', 'date_format:Y-m-d'],
            'expire_date' => ['required', 'date', 'date_format:Y-m-d']
        ];
    }
}
