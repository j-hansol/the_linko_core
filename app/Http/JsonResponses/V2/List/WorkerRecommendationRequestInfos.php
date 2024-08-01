<?php

namespace App\Http\JsonResponses\V2\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Http\JsonResponses\V2\Base\WorkerRecommendationRequestInfo;
use App\Lib\PageResult;
use App\Models\WorkerRecommendationRequest;
use OpenApi\Annotations as OA;

class WorkerRecommendationRequestInfos extends PageResponse {
    /**
     * @OA\Schema(
     * *     schema="worker_recommendation_request_infos",
     * *     title="근로자 추천 요청 목록",
     * *     @OA\Property(
     * *         property="items",
     * *         type="array",
     * *         @OA\Items(ref="#/components/schemas/worker_recommendation_request_info")
     * *    )
     * * )
     */
    function __construct(PageResult $result) {
        $data = [];
        foreach ($result->collection as $t)
            if($t instanceof WorkerRecommendationRequest) $data[] = WorkerRecommendationRequestInfo::toArray($t);
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
