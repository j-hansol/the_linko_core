<?php

namespace App\Http\JsonResponses\V2\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Http\JsonResponses\V2\Base\WorkerRecommendationInfo;
use App\Lib\PageResult;
use App\Models\WorkerRecommendation;
use OpenApi\Annotations as OA;

class WorkerRecommendationInfos extends PageResponse {
    /**
     * 추천 목록을 리턴한다.
     * @param PageResult $result
     * @OA\Schema(
     *     schema="worker_recommendation_infos",
     *     title="근로자 추천 목록",
     *     @OA\Property(
     *         property="items",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/worker_recommendation_info")
     *    )
     * )
     */
    function __construct(PageResult $result) {
        $data = [];
        foreach ($result->collection as $t) {
            if($t instanceof WorkerRecommendation) $data[] = WorkerRecommendationInfo::toArray($t);
        }
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
