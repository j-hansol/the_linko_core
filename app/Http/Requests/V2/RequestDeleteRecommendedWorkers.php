<?php

namespace App\Http\Requests\V2;

use App\Rules\ExistsValues;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestDeleteRecommendedWorkers extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *      schema="input_delete_recommended_workers",
     *      title="추츤 근로자 정보 삭제 입력",
     *      @OA\Property(property="recommended_worker_ids", type="array", description="삭제 대상 근로자 정보 일련번호 목록", @OA\Items(type="integer")),
     *      required={"recommended_worker_ids"}
     *  )
     */
    public function rules(): array {
        return [
            'recommended_worker_ids' => ['required', new ExistsValues('recommended_workers', 'id')]
        ];
    }
}
