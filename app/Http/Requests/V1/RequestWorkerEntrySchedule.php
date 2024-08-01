<?php

namespace App\Http\Requests\V1;

use App\Rules\ExistsValueInFilter;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestWorkerEntrySchedule extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검차 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="input_worker_entry_schedule",
     *     title="근로자 입국일정 설정",
     *     @OA\Property (
     *         property="entry_schedule_id",
     *         type="integer",
     *         description="입국일정 정보 일련번호"
     *     ),
     *     @OA\Property (
     *         property="assigned_worker_ids",
     *         type="array",
     *         @OA\Items (type="integer"),
     *         description="배정 근로자정보 일련번호 목록"
     *      ),
     *     required={"input_worker_entry_schedule","assigned_worker_ids"}
     * )
     */
    public function rules(): array {
        $contract_id = $this->id;
        return [
            'entry_schedule_id' => ['required', 'integer', 'exists:entry_schedules,id'],
            'assigned_worker_ids' => ['required',
                new ExistsValueInFilter('assigned_workers', 'id',
                    [['field' => $contract_id, 'operator' => '=', $contract_id]])
            ]
        ];
    }
}
