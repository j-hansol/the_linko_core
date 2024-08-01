<?php

namespace App\Http\Requests\V1;

use App\Rules\ExistsValues;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestAddWorkingCompanies extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 근무 기업 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="input_working_companies",
     *     title="채용 기업 입력",
     *     @OA\Property(
     *          property="working_company_ids",
     *          type="array",
     *          description="채용 기업 계정 일련번호 (컴파로 구분하여 복수 입력 가능)",
     *          @OA\Items(type="integer")
     *     ),
     *     @OA\Property(
     *          property="planned_worker_count",
     *          type="integer",
     *          description="채영 계약 근로자 수"
     *     ),
     *     required={"working_company_ids", "planned_worker_count"}
     * )
     */
    public function rules(): array {
        return [
            'working_company_ids' => ['required', new ExistsValues('users', 'id')],
            'planned_worker_count' => ['required', 'integer']
        ];
    }
}
