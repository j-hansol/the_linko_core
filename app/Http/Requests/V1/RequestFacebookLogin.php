<?php

namespace App\Http\Requests\V1;

use App\Lib\DeviceType;
use App\Rules\ValidCryptData;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestFacebookLogin extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 페이스북 로그인 시 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="facebook_login",
     *     title="패이스북 로그인",
     *     @OA\Property (
     *          property="email",
     *          type="string",
     *          description="이메일 주소 (암호화 필요)"
     *     ),
     *     @OA\Property (
     *          property="provider",
     *          type="string",
     *          description="SNS 로그인 제공자 이름 (facebook만 지원)"
     *     ),
     *     @OA\Property (
     *          property="identifier",
     *          type="string",
     *          description="SNS에서 제공하는 ID"
     *     ),
     *     required={"email", "provider", "identifier"}
     * )
     */
    public function rules(): array {
        return [
            'email' => ['required', new ValidCryptData()],
            'provider' => ['required', 'string'],
            'identifier' => ['required', 'string'],
            'device_type' => ['required', new Enum(DeviceType::class)],
            'device_name' => [$this->input('device_type') == DeviceType::TYPE_MOBILE->value ? 'required' : 'nullable', 'string'],
            'uuid' => [$this->input('device_type') == DeviceType::TYPE_MOBILE->value ? 'required' : 'nullable'],
            'fcm' => ['nullable', 'string']
        ];
    }
}
