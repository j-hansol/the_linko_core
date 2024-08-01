<?php

namespace App\Http\JsonResponses\V1\List;

use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class AvailableVisaApplicationStatus extends JsonResponse {
    /**
     * @param array $status
     * @OA\Schema (
     *     schema="available_visa_application_status",
     *     title="설정 가능한 비자 발급 상태 번호 목록",
     *     @OA\Property (
     *         property="available_status",
     *         type="array",
     *         @OA\Items(type="integer")
     *     )
     * )
     */
    public function __construct(array $status = []) {
        parent::__construct([
            'message' => __('api.r200'),
            'available_status' => $status
        ]);
    }
}
