<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use App\Traits\V1\RuleForRegisterUser;
use Illuminate\Foundation\Http\FormRequest;

class RequestLoginAuto extends FormRequest {
    use RequestValidation, RuleForRegisterUser;

    public function authorize(): bool {return true;}

    /**
     * 단말기 유효성 감사 규칙을 리턴한다.
     * @return array
     */
    public function rules(): array {
        return $this->getDeviceInfoRule();
    }
}
