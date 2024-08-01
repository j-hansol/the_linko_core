<?php

namespace App\Http\Requests\V2;

use App\Rules\ExistsValues;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestAddRecommendedWorkers extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="input_add_recommended_workers",
     *     title="추츤 근로자 정보 추가 입력",
     *     @OA\Property(property="worker_ids", type="array", description="추가 대상 근로자 계정 일련번호 목록", @OA\Items(type="integer")),
     *     required={"worker_ids"}
     * )
     */
    public function rules(): array {
        return [
            'worker_ids' => ['required', new ExistsValues('users', 'id')]
        ];
    }
}
