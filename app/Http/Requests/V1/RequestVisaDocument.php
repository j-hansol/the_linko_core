<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

class RequestVisaDocument extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 설정한다.
     * @return array
     * @OA\Schema(
     *     schema="updatable_visa_document",
     *     title="등록 가능한 비자발급용 문서정보 항목",
     *     @OA\Property(
     *          property="type_id",
     *          type="integer",
     *          description="비자발급용 문서 유형 일련번호"
     *     ),
     *     @OA\Property(
     *          property="title",
     *          type="string",
     *          description="문서 제목"
     *     ),
     *     @OA\Property(
     *          property="file_path",
     *          type="string",
     *          format="binary",
     *          description="등록 문서"
     *     ),
     *     required={"type_id", "title", "file_path"}
     * )
     */
    public function rules(): array {
        return [
            'type_id' => ['required', 'integer', 'exists:visa_document_types,id'],
            'title' => ['required'],
            'file_path' => ['required', 'file']
        ];
    }
}
