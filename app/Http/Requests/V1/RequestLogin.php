<?php

namespace App\Http\Requests\V1;

use App\Lib\DeviceType;
use App\Rules\ValidCryptData;
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
     *          description="회원코드 (암호화 필요)"
     *     ),
     *     @OA\Property (
     *          property="password",
     *          type="string",
     *          description="비밀빈호 (암호화 필요)"
     *     ),
     *     required={"id_alias", "password"}
     * )
     */
    public function rules(): array {
        return [
            'id_alias' => ['required', new ValidCryptData()],
            'password' => ['required', new ValidCryptData()],
            'device_type' => ['required', new Enum(DeviceType::class)],
            'uuid' => [$this->input('device_type') == DeviceType::TYPE_MOBILE->value ? 'required' : 'nullable'],
            'fcm' => ['nullable', 'string']
        ];
    }
}
