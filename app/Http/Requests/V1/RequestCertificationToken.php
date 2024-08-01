<?php

namespace App\Http\Requests\V1;

use App\Lib\CertificationTokenFunction;
use App\Rules\CryptDataExists;
use App\Rules\ValidCryptData;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestCertificationToken extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 비밀번호 발급 또는 변경을 위한 토큰을 요청하기 위한 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *      schema="certification_function",
     *      title="인증 토큰필요 기능",
     *      @OA\Property (
     *           property="certification_function",
     *           ref="#/components/schemas/CertificationTokenFunction",
     *           description="인증 토큰필요 기능"
     *      ),
     *      required={"certification_function"}
     *  )
     */
    public function rules(): array {
        return [
            'email' => ['required', (new ValidCryptData())->type('email'), new CryptDataExists('users', 'email')],
            'certification_function' => ['required', new Enum(CertificationTokenFunction::class)]
        ];
    }
}
