<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestPassportFile extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="input_passport_file",
     *     title="여권 파일 등록",
     *     @OA\Property (property="file", type="string", format="binary", description="여권 이미지 또는 파일")
     * )
     */
    public function rules(): array {
        return [
            'file' => ['required', 'file', 'mimetypes:application/pdf,image/png,image/jpeg']
        ];
    }
}
