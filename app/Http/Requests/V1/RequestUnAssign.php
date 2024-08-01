<?php

namespace App\Http\Requests\V1;

use App\Rules\ExistsValues;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestUnAssign extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 감사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="input_unassign_worker",
     *     title="기업 배정 취소 근로자 입력",
     *     @OA\Property (
     *         property="assigned_worker_ids",
     *         type="array",
     *         @OA\Items (type="integer"),
     *         description="근로자 배정 정보 일련번호 목록"
     *     )
     * )
     */
    public function rules(): array {
        return [
            'assigned_worker_ids' => ['required', 'string', new ExistsValues('assigned_workers', 'id')]
        ];
    }
}
