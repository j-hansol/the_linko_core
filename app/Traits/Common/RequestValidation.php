<?php

namespace App\Traits\Common;

use App\Http\JsonResponses\Common\ErrorMessage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait RequestValidation {
    /**
     * 유효성 검사 오류 시 JsonResponse 객체를 리턴하도록 한다.
     * @param Validator $validator
     * @return void
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator) : void {
        throw new ValidationException($validator, new ErrorMessage($validator->errors()->toArray()));
    }
}
