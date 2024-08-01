<?php

namespace App\Http\Requests\V1;

use App\Rules\ExistsValues;
use App\Rules\ValidCryptData;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;

class RequestUpdateWorker extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     */
    public function rules(): array {
        return [
            'email' => ['nullable', (new ValidCryptData())->type('email')->unique('users', 'email')],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'cell_phone' => ['nullable', new ValidCryptData()],
            'address' => ['nullable', new ValidCryptData()],
            'family_name' => ['required'],
            'given_names' => ['required'],
            'identity_no' => ['nullable', new ValidCryptData()],
            'sex' => ['required', 'in:M,F'],
            'birthday' => ['nullable', 'date', 'date_format:Y-m-d'],
            'birth_country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'another_nationality_ids' => ['nullable', new ExistsValues('countries', 'id')],
            'management_org_id' => ['nullable', 'integer', 'exists:users,id'],
            'password' => [(new ValidCryptData())->required(true)]
        ];
    }
}
