<?php

namespace App\Http\Requests\V1;

use App\Lib\AssignedWorkerStatus;
use App\Rules\ExistsValues;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class RequestUpdateWorkerStatus extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     */
    public function rules(): array{
        return [
            'worker_ids' => ['required', new ExistsValues('users', 'id')],
            'status' => ['required', new Enum(AssignedWorkerStatus::class)]
        ];
    }
}
