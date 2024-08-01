<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestSetWorkerInfo extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}


    /**
     * 유효성 검사 규칙을 설정한다.
     * @return array
     * @OA\Schema (
     *     schema="updatable_worker_info",
     *     title="근로자 정보 등록 항목",
     *     @OA\Property(property="skills", type="string", description="보유 기술"),
     *     @OA\Property(property="jobs", type="string", description="직업"),
     *     @OA\Property(property="hobby", type="string", description="취미"),
     *     @OA\Property(property="education_part", type="string", description="휘망 교육 분야"),
     *     @OA\Property(property="medical_support", type="integer", enum={0,1}, description="의료지원 여부 (0:비희망, 1:지원희망)"),
     *     @OA\Property(property="height", type="number", format="double", description="신장 (단위: 미터(m))"),
     *     @OA\Property(property="weight", type="number", format="double",  description="몸무게 (단위: 킬로그람(kg))"),
     *     @OA\Property(property="blood_type", type="string", description="혈액형"),
     *     @OA\Property(property="bith_place", type="string", description="출생지"),
     *     @OA\Property(property="civil_status", type="string", description="시민 신분"),
     *     @OA\Property(property="religion", type="string", description="종교"),
     *     @OA\Property(property="language", type="string", description="언어"),
     *     @OA\Property(property="region", type="string", description="구역"),
     *     @OA\Property(property="current_address", type="string", description="현 거주지"),
     *     @OA\Property(property="spouse", type="string", description="배우자 이름"),
     *     @OA\Property(property="children_names", type="string", description="자녀 이름")
     * )
     */
    public function rules(): array {
        return [
            'medical_support' => ['nullable', 'boolean'],
            'height' => ['nullable', 'numeric'],
            'weight' => ['nullable', 'numeric']
        ];
    }
}
