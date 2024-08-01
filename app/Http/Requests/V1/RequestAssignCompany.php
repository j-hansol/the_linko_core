<?php

namespace App\Http\Requests\V1;

use App\Rules\ExistsValues;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestAssignCompany extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="input_assign_worker",
     *     title="근로자 기업 배정 입력",
     *     @OA\Property (
     *          property="company_user_id",
     *          type="integer",
     *          description="참여 기업 계정 일련번호"
     *     ),
     *     @OA\Property (
     *          property="assigned_worker_ids",
     *          type="array",
     *          @OA\Items (type="integer"),
     *          description="근로자 배정 정보 일련번호 목록"
     *     ),
     *     required={"company_user_id", "assigned_worker_ids"}
     * )
     */
    public function rules(): array {
        return [
            'company_user_id' => ['required', 'integer', 'exists:working_companies,company_user_id'],
            'assigned_worker_ids' => ['required', new ExistsValues('assigned_workers', 'id')]
        ];
    }
}
