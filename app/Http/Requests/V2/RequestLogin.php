<?php

namespace App\Http\Requests\V2;

use App\Lib\DeviceType;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestLogin extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 로그인 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="login",
     *     title="로그인",
     *     @OA\Property (
     *          property="id_alias",
     *          type="string",
     *          description="회원코드"
     *     ),
     *     @OA\Property (
     *          property="password",
     *          type="string",
     *          description="비밀빈호"
     *     ),
     *     required={"id_alias", "password"}
     * )
     */
    public function rules(): array {
        return [
            'id_alias' => ['required'],
            'password' => ['required'],
            'device_type' => ['required', new Enum(DeviceType::class)],
            'uuid' => [$this->input('device_type') == DeviceType::TYPE_MOBILE->value ? 'required' : 'nullable'],
            'fcm' => ['nullable', 'string']
        ];
    }
}
