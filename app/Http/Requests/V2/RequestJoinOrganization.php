<?php

namespace App\Http\Requests\V2;

use App\Traits\Common\RequestValidation;
use App\Traits\V2\RuleForRegisterUser;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestJoinOrganization extends FormRequest {
    use RequestValidation, RuleForRegisterUser;

    public function authorize(): bool {return true;}

    /**
     * Get the validation rules that apply to the request.
     * @return array
     * @OA\Schema (
     *     schema="join_organization_profile",
     *     title="회원가입 단체 프로필",
     *     @OA\Property (
     *          property="name",
     *          type="string",
     *          description="단체 이름"
     *     ),
     *     @OA\Property (
     *          property="registration_no",
     *          type="string",
     *          description="등록번호 (예: 사업자 등록번호)"
     *     ),
     *     @OA\Property (
     *          property="boss_name",
     *          type="string",
     *          description="대표자 이름"
     *     ),
     *     @OA\Property (
     *          property="manager_name",
     *          type="string",
     *          description="담당자 이름, 암호화 필요"
     *     ),
     *     @OA\Property (
     *          property="telephone",
     *          type="string",
     *          description="전화번호"
     *     ),
     *     @OA\Property (
     *          property="fax",
     *          type="string",
     *          description="팩스번호"
     *     ),
     *     @OA\Property (
     *          property="longitude",
     *          type="number",
     *          format="double",
     *          description="경도"
     *     ),
     *     @OA\Property (
     *          property="latitude",
     *          type="number",
     *          format="double",
     *          description="위도"
     *     ),
     *     required={"name", "registration_no", "boss_name", "manager_name"}
     * )
     */
    public function rules(): array {
        return $this->getCommonRule() + $this->getOrganizationUserTypeRule() + $this->getLoginMethodRule() +
            $this->getOrganizationInfoRule() + $this->getPasswordRule() + $this->getDeviceInfoRule();
    }
}
