<?php

namespace App\Http\Requests\V1;

use App\Rules\ExistsValues;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestUpdatePlannedWorkerCount extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 채영 기업 계획 근로자 수 변경 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="input_update_working_company",
     *     title="채영 기업 근로자 수 변경 정보",
     *     @OA\Property(
     *          property="ids",
     *          type="array",
     *          description="채용 계획 정보 일련번호 (컴파로 구분하여 복수 입력 가능)",
     *          @OA\Items(type="integer")
     *     ),
     *     @OA\Property(
     *          property="planned_worker_count",
     *          type="integer",
     *          description="채영 계약 근로자 수"
     *     ),
     *     required={"ids", "planned_worker_count"}
     * )
     * )
     */
    public function rules(): array {
        return [
            'ids' => ['required', new ExistsValues('working_companies', 'id')],
            'planned_worker_count' => ['required', 'integer']
        ];
    }
}
