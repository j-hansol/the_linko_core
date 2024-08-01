<?php

namespace App\Http\Requests\V2;

use App\Traits\Common\RequestValidation;
use App\Traits\V2\RuleForRegisterUser;
use Illuminate\Foundation\Http\FormRequest;

class RequestUpdateOrganizationProfile extends FormRequest {
    use RequestValidation, RuleForRegisterUser;

    public function authorize(): bool {return true;}

    /**
     * 단체 프로필 변경을 위한 유효성 검사 규칙을 리턴한다.
     * @return array
     * @see RequestJoinOrganization
     */
    public function rules(): array {
        return $this->getEditableCommonRule() + $this->getOrganizationInfoRule();
    }
}
