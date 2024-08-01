<?php

namespace App\Http\Requests\V2;

use App\Lib\RecommendedWorkerStatus;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestRecommendedWorkerStatus extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="input_recommended_worker_status",
     *     title="추천 근로자 상태 입력",
     *     @OA\Property(property="status", ref="#/components/schemas/RecommendedWorkerStatus")
     * )
     */
    public function rules(): array {
        return [
            'status' => ['required', new Enum(RecommendedWorkerStatus::class)]
        ];
    }
}
