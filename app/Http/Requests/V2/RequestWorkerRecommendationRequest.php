<?php

namespace App\Http\Requests\V2;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestWorkerRecommendationRequest extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="input_worker_recommendation_request",
     *     title="근로자 추천 요청 입력",
     *     @OA\Property(property="occupational_group_id", type="integer", description="요청 직업군 ID"),
     *     @OA\Property(property="title", type="string", description="요청 제목"),
     *     @OA\Property(property="body", type="string", description="요청 내용"),
     *     @OA\Property(property="worker_count", type="integer", description="추천 요청 근로자 수")
     * )
     */
    public function rules(): array {
        return [
            'occupational_group_id' => ['required', 'integer', 'exists:occupational_groups,id'],
            'title' => ['required'],
            'body' => ['required'],
            'worker_count' => ['required', 'integer', 'min:1'],
        ];
    }
}
