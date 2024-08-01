<?php

namespace App\Http\Requests\V2;

use App\DTOs\V2\WorkerRecommendationRequestStatusDto;
use App\Lib\WorkerRecommendationRequestStatus;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestWorkerRecommendationRequestStatus extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="set_worker_recommendation_request_status",
     *     title="근로자 추천 요청 승인 상태 설정",
     *     @OA\Property(property="status", ref="#/components/schemas/WorkerRecommendationRequestStatus"),
     *     required={"status"}
     * )
     */
    public function rules(): array {
        return [
            'status' => ['required', 'integer', new Enum(WorkerRecommendationRequestStatus::class)]
        ];
    }
}
