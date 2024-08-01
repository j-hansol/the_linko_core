<?php

namespace App\Http\Requests\V1;

use App\Rules\ExistsValues;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;

class RequestDeleteAssignedWorkers extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array[]
     */
    public function rules(): array{
        return [
            'worker_ids' => ['required', new ExistsValues('users', 'id')]
        ];
    }
}
