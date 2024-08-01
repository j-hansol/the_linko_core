<?php

namespace App\Http\Requests\V1;

use App\Lib\ActionPointType;
use App\Lib\WorkerActionPointSetReason;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class RequestWorkerActionPoint extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     */
    public function rules(): array {
        return [
            'reason' => ['required', new Enum(WorkerActionPointSetReason::class)],
            'type' => ['required', new Enum(ActionPointType::class)],
            'name' => ['required', 'string'],
            'address' => ['required', 'string'],
            'longitude' => ['required', 'numeric'],
            'latitude' => ['required', 'numeric'],
            'radius' => ['required', 'numeric', 'min:0.1']
        ];
    }
}
