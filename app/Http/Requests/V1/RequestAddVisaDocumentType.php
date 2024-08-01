<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestAddVisaDocumentType extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 오류 시 JsonResponse 객체를 리턴하도록 한다.
     * @return array
     * @OA\Schema(
     *     schema="updatable_visa_document_type",
     *     title="등록 가능한 비자발급시 필요한 문서 정보 항목",
     *     @OA\Property(
     *          property="name",
     *          type="string",
     *          description="문서유형 이름"
     *     ),
     *     @OA\Property(
     *          property="en_name",
     *          type="string",
     *          description="문서유형 이름(영문)"
     *     ),
     *     @OA\Property(
     *          property="description",
     *          type="string",
     *          description="유형 설명"
     *     ),
     *     @OA\Property(
     *          property="en_description",
     *          type="string",
     *          description="유형 설명(영문)"
     *     ),
     *     @OA\Property(
     *          property="active",
     *          type="integer",
     *          enum={"0","1"},
     *          description="사용여부"
     *     ),
     *     required={"name", "en_name", "active"}
     * )
     */
    public function rules(): array {
        return [
            'name' => ['required'],
            'en_name' => ['required'],
            'active' => ['required', 'boolean']
        ];
    }
}
