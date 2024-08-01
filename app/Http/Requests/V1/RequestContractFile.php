<?php

namespace App\Http\Requests\V1;

use App\Lib\ContractFileGroup;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestContractFile extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 계약관련 파일 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="add_contract_file",
     *     title="계약관련 파일 추가",
     *     @OA\Property (
     *          property="title",
     *          type="string",
     *          description="파일 제목"
     *     ),
     *     @OA\Property (
     *          property="file_group",
     *          ref="#/components/schemas/ContractFileGroup",
     *     ),
     *     @OA\Property (
     *          property="file",
     *          type="string",
     *          format="binary",
     *          description="첨부파일"
     *     )
     * )
     */
    public function rules(): array {
        return [
            'title' => ['required', 'string'],
            'file_group' => ['required', new Enum(ContractFileGroup::class)],
            'file' => ['required', 'file']
        ];
    }
}
