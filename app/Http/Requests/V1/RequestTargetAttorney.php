<?php

namespace App\Http\Requests\V1;

use App\Rules\ExistsValues;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestTargetAttorney extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사를 진행한다.
     * @return array
     * @OA\Schema (
     *     schema="visa_ids",
     *     title="컨설팅 권한 요청 또는 행정사 배정을 위한비자발급 정보 일련번호",
     *     @OA\Property (
     *          property="visa_ids",
     *          type="array",
     *          @OA\Items (type="integer"),
     *          description="비자발급 정보 일련번호"
     *     )
     * )
     */
    public function rules(): array {
        return ['visa_ids' => ['required', new ExistsValues('visa_applications', 'id')]];
    }
}
