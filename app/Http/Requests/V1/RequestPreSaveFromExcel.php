<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestPreSaveFromExcel extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="presave_worker_from_excel",
     *     title="엑셀파일로부터 근로자 정보 등록 항목",
     *     @OA\Property (
     *          property="file",
     *          type="string",
     *          format="binary",
     *          description="엑셀파일 (암호화 필요)"
     *     ),
     *     @OA\Property (
     *          property="create_account",
     *          type="integer",
     *          enum={"0","1"},
     *          description="계정생성 가능한 경우 생성 (0:생성안함, 1:생성)"
     *     ),
     *     required={"file", "create_account"}
     * )
     */
    public function rules(): array {
        return [
            'file' => ['required', 'file'],
            'create_account' => ['required', 'boolean']
        ];
    }
}
