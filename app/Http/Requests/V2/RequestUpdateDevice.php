<?php

namespace App\Http\Requests\V2;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestUpdateDevice extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    /**
     * 단말기 정보 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="update_device",
     *     title="단말기 정보 수정",
     *     @OA\Property (
     *          property="device_name",
     *          type="string",
     *          description="단말기 이름 (단말기 종류가 모바일인 경우 필수)"
     *     ),
     *     @OA\Property (
     *          property="uuid",
     *          type="string",
     *          description="단말기 식별 UUID (단말기 종류가 모바일인 경우 필수)"
     *     ),
     *     @OA\Property (
     *          property="fcm",
     *          type="string",
     *          description="FCM 토큰 (선택입력)"
     *     ),
     *     required={"device_name","uuid"}
     * )
     */
    public function rules(): array {
        return [
            'device_name' => ['required', 'string'],
            'uuid' => ['required', 'string'],
            'fcn' => ['nullable', 'string']
        ];
    }
}
