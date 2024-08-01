<?php

namespace App\Http\Requests\V1;

use App\Rules\ExistsValues;
use App\Traits\Common\CheckVisaForFormRequest;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;

class RequestVisaVisitCountryIds extends FormRequest {
    use RequestValidation, CheckVisaForFormRequest;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     */
    public function rules(): array {
        return ['ids' => [(new ExistsValues('worker_visits', 'id'))->nullable()]];
    }
}
