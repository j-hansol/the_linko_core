<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;

class RequestReportForOrderedTask extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    public function rules(): array {
        return [
            'title' => ['required', 'string'],
            'body' => ['required', 'string'],

        ];
    }
}
