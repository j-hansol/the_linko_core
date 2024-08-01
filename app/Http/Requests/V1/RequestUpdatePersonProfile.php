<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use App\Traits\V1\RuleForRegisterUser;
use Illuminate\Foundation\Http\FormRequest;

class RequestUpdatePersonProfile extends FormRequest {
    use RequestValidation, RuleForRegisterUser;

    public function authorize(): bool {return true;}

    /**
     * 개인 프로필 변경을 위한 유효성 검사 규칙을 리턴한다.
     * @return array
     * @see RequestJoinWorker
     * @see RuleForRegisterUser::getManagementOrgRule()
     */
    public function rules(): array {
        return $this->getEditableCommonRule() + $this->getPersonInfoRule();
    }
}
