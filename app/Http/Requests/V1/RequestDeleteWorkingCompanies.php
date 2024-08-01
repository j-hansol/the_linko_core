<?php

namespace App\Http\Requests\V1;

use App\Rules\ExistsValues;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;

class RequestDeleteWorkingCompanies extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 채용 기업 정보 삭제를 유한 유효성 검사 규칙을 리턴한다.
     * @return array
     */
    public function rules(): array {
        return [
            'ids' => ['required', new ExistsValues('working_companies', 'id')],
        ];
    }
}
