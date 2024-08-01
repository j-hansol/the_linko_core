<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestWorkerVisitedCountry extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 설정한다.
     * @return array
     * @OA\Schema(
     *     schema="updatable_visited_country",
     *     title="근로자 방문국가정보 등록 항목",
     *     @OA\Property(
     *          property="country_id",
     *          type="integer",
     *          description="국가정보 일련번호"
     *     ),
     *     @OA\Property(
     *          property="visit_purpose",
     *          type="string",
     *          description="방문목적"
     *     ),
     *     @OA\Property(
     *          property="entry_date",
     *          type="string",
     *          format="date",
     *          description="입국일자"
     *     ),
     *     @OA\Property(
     *          property="departure_date",
     *          type="string",
     *          format="date",
     *          description="출국일자"
     *     ),
     *     required={"country_id", "visit_purpose", "entry_date", "departure_date"}
     * )
     */
    public function rules(): array {
        return [
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'visit_purpose' => ['required'],
            'entry_date' => ['required'],
            'departure_date' => ['required']
        ];
    }
}
