<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestEntrySchedule extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 입국일정 정보 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="input_entry_ifo",
     *     title="입국일정 정보 입력",
     *     @OA\Property (
     *          property="entry_date",
     *          type="string",
     *          format="date",
     *          description="입국일자"
     *     ),
     *     @OA\Property (
     *          property="entry_limit",
     *          type="integer",
     *          description="입국 정원"
     *     ),
     *     @OA\Property (
     *          property="target_datetime",
     *          type="string",
     *          format="date-time",
     *          description="집결지 도착 일시"
     *     ),
     *     @OA\Property (
     *          property="target_place",
     *          type="string",
     *          description="집결지"
     *     )
     * )
     */
    public function rules(): array {
        return [
            'entry_date' => ['required', 'date', 'date_format:Y-m-d'],
            'entry_limit' => ['required', 'integer', 'min:1'],
            'target_datetime' => ['required', 'date', 'date_format:Y-m-d H:i:s'],
            'target_place' => ['required', 'string']
        ];
    }
}
