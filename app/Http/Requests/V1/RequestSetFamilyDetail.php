<?php

namespace App\Http\Requests\V1;

use App\Lib\MaritalStatus;
use App\Rules\ValidCryptData;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestSetFamilyDetail extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 설정한다.
     * @return array
     * @OA\Schema(
     *     schema="updatable_visa_family_detail",
     *     title="비자신청을 위한 결온여부 및 가족정보 등록 항목",
     *     @OA\Property(
     *          property="marital_status",
     *          type="integer",
     *          description="결혼여부",
     *          ref="#/components/schemas/MaritalStatus"
     *     ),
     *     @OA\Property(
     *          property="spouse_family_name",
     *          type="string",
     *          description="배우자 성"
     *     ),
     *     @OA\Property(
     *          property="spouse_given_name",
     *          type="string",
     *          description="배우자 이름"
     *     ),
     *     @OA\Property(
     *          property="spouse_birthday",
     *          type="string",
     *          format="date",
     *          description="배우자 생년월일"
     *     ),
     *     @OA\Property(
     *          property="spouse_nationality_id",
     *          type="integer",
     *          description="배우자 국적"
     *     ),
     *     @OA\Property(
     *          property="spouse_residential_address",
     *          type="string",
     *          description="배우자 실 거주지 주소 (암호화 필요)"
     *     ),
     *     @OA\Property(
     *          property="spouse_contact_no",
     *          type="string",
     *          description="배우자 연락처 (암호화 필요)"
     *     ),
     *     @OA\Property(
     *          property="number_of_children",
     *          type="integer",
     *          description="자녀 수"
     *     ),
     *     required={"marital_status"}
     * )
     */
    public function rules(): array {
        $status = $this->enum('marital_status', MaritalStatus::class);
        return [
            'marital_status' => ['required', new Enum(MaritalStatus::class)],
            'spouse_family_name' => [$this->getRule($status == MaritalStatus::MARRIED)],
            'spouse_given_name' => [$this->getRule($status == MaritalStatus::MARRIED)],
            'spouse_birthday' => [$this->getRule($status == MaritalStatus::MARRIED), 'date', 'date_format:Y-m-d'],
            'spouse_nationality_id' => [$this->getRule($status == MaritalStatus::MARRIED), 'integer', 'exists:countries,id'],
            'spouse_residential_address' => [(new ValidCryptData())->requiredOrNull($status == MaritalStatus::MARRIED)],
            'spouse_contact_no' => [(new ValidCryptData())->requiredOrNull($status == MaritalStatus::MARRIED)],
            'number_of_children' => ['nullable', 'integer', 'min:0']
        ];
    }

    private function getRule(bool $flag) : string {
        return $flag ? 'required' : 'nullable';
    }
}
