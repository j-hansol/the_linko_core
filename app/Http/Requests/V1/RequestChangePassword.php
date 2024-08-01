<?php

namespace App\Http\Requests\V1;

use App\Rules\ValidCryptData;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestChangePassword extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 비밀번호 변경을 위한 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="change_password",
     *     title="비밀번호 변경",
     *     @OA\Property (
     *          property="current_password",
     *          type="string",
     *          description="기존 비밀빈호"
     *     ),
     *     @OA\Property (
     *          property="password",
     *          type="string",
     *          description="새 비밀번호"
     *     )
     * )
     */
    public function rules(): array {
        return [
            'current_password' => ['required', new ValidCryptData()],
            'password' => ['required', new ValidCryptData()]
        ];
    }
}
