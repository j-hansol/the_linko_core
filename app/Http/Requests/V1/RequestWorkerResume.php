<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

class RequestWorkerResume extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="input_worker_resume",
     *     title="근로자 이력서 등록",
     *     @OA\Property(property="file", type="string", format="binary", description="이력서 파일"),
     *     required={"file"}
     * )
     */
    public function rules(): array {
        Log::info('이력서 등록 파일', $this->all());
        return [
            'file' => ['required', 'file']
        ];
    }
}
