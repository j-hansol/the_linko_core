<?php

namespace App\Http\JsonResponses\V1\Base;

use Illuminate\Http\JsonResponse;
use App\Models\WorkerInfo;
use OpenApi\Annotations as OA;

class WorkerInfoResponse extends JsonResponse {
    function __construct(?WorkerInfo $info) {
        parent::__construct($info ? static::toArray($info) : static::NullInfo());
    }

    /**
     * 근로자 추가정보를 배열로 리턴한다.
     * @param WorkerInfo $info
     * @return array
     * @OA\Schema(
     *     schema="worker_info",
     *     title="근로자 추가정보",
     *     @OA\Property(property="user_id", type="integer", description="근로자 계정 일련번호"),
     *     @OA\Property(property="skills", type="string", description="보유 기술"),
     *     @OA\Property(property="jobs", type="string", description="직업"),
     *     @OA\Property(property="hobby", type="string", description="취미"),
     *     @OA\Property(property="education_part", type="string", description="휘망 교육 분야"),
     *     @OA\Property(property="medical_support", type="integer", description="의료 지원 여부"),
     *     @OA\Property(property="height", type="number", format="float", description="키"),
     *     @OA\Property(property="weight", type="number", format="float", description="몸무게"),
     *     @OA\Property(property="blood_type", type="string", description="혈액형"),
     *     @OA\Property(property="birth_place", type="string", description="출생지"),
     *     @OA\Property(property="civil_status", type="string", description="시민 신분"),
     *     @OA\Property(property="religion", type="string", description="종교"),
     *     @OA\Property(property="language", type="string", description="언어 / 방언"),
     *     @OA\Property(property="region", type="string", description="구역"),
     *     @OA\Property(property="current_address", type="string", description="현 거주지"),
     *     @OA\Property(property="spouse", type="string", description="배우자 이름"),
     *     @OA\Property(property="children_names", type="string", description="자녀 이름"),
     * )
     */
    public static function toArray(WorkerInfo $info) : array {
        return $info->toArray();
    }

    public static function NullInfo() : array {
        return [
            'user_id' => null,
            'skills' => null,
            'jobs' => null,
            'hobby' => null,
            'education_part' => null,
            'medical_support' => 0,
            'height' => null,
            'weight' => null,
            'blood_type' => null,
            'birth_place' => null,
            'civil_status' => null,
            'religion' => null,
            'language' => null,
            'region' => null,
            'current_address' => null,
            'spouse' => null,
            'children_names' => null
        ];
    }
}
