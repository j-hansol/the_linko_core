<?php

namespace App\Http\Requests\V1;

use App\Rules\ValidCryptData;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestResetPassword extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 비밀번호 변경 요청을 위한 유효성 감사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *      schema="password",
     *      title="비밀번호",
     *      @OA\Property (
     *           property="password",
     *           type="string",
     *           description="비밀번호"
     *      ),
     *      required={"password"}
     *  )
     * @OA\Schema (
     *      schema="certification_token",
     *      title="인증 토큰",
     *      @OA\Property (
     *           property="token",
     *           type="string",
     *           description="인증 토큰"
     *      ),
     *      required={"token"}
     *  )
     */
    public function rules(): array {
        return [
            'email' => ['required', new ValidCryptData()],
            'token' => ['required'],
            'password' => ['required', New ValidCryptData()]
        ];
    }
}
