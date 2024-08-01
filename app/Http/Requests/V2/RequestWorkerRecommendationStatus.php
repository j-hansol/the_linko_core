<?php

namespace App\Http\Requests\V2;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestWorkerRecommendationStatus extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="input_worker_recommendation_status",
     *     title="근로자 추천 정보 상태 설정",
     *     @OA\Property(property="active", type="boolean", description="사용 여부"),
     *     required={"active"}
     * )
     */
    public function rules(): array {
        return [
            'active' => ['required', 'boolean']
        ];
    }
}
