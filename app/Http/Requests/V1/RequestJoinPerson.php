<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use App\Traits\V1\RuleForRegisterUser;
use Illuminate\Foundation\Http\FormRequest;

class RequestJoinPerson extends FormRequest {
    use RequestValidation, RuleForRegisterUser;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     */
    public function rules(): array {
        return $this->getCommonRule() + $this->getPersonUserTypeRule() + $this->getLoginMethodRule() +
            $this->getPersonInfoRule() + $this->getManagementOrgRule() + $this->getPasswordRule() +
            $this->getDeviceInfoRule();
    }
}
