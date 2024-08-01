<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

class RequestWorkerExperience extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="input_worker_experience",
     *     title="근로자 경력정보 입력",
     *     @OA\Property(property="company_name", type="string", description="근무 기업명"),
     *     @OA\Property(property="company_address", type="string", description="근무 기업 주소"),
     *     @OA\Property(property="task", type="string", description="업무"),
     *     @OA\Property(property="part", type="string", description="부서"),
     *     @OA\Property(property="position", type="string", description="직위/직급"),
     *     @OA\Property(property="job_description", type="string", description="업무 설명"),
     *     @OA\Property(property="start_date", type="string", format="date", description="근무 시작일"),
     *     @OA\Property(property="end_date", type="string", format="date", description="'근무 종료일"),
     *     @OA\Property(property="file", type="string", format="binary", description="경력 증명서(정빙서류)"),
     *     required={"company_name", "start_date"}
     * )
     */
    public function rules(): array {
        return [
            'company_name' => ['required'],
            'company_address' => ['nullable'],
            'task' => ['nullable'],
            'part' => ['nullable'],
            'position' => ['nullable'],
            'job_description' => ['nullable'],
            'start_date' => ['required', 'date', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'file' => ['nullable', 'file']
        ];
    }
}
