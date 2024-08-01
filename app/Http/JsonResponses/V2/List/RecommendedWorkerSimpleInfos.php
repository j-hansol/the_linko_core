<?php

namespace App\Http\JsonResponses\V2\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Http\JsonResponses\V2\Base\RecommendedWorkerSimpleInfo;
use App\Http\QueryParams\ListQueryParam;
use App\Lib\PageResult;
use App\Models\WorkerRecommendation;
use OpenApi\Annotations as OA;

class RecommendedWorkerSimpleInfos extends PageResponse {
    /**
     * @OA\Schema (
     *     schema="recommended_worker_simple_infos",
     *     title="추천 근로자 간략한 정보 목록",
     *     @OA\Property (
     *          property="items",
     *          type="array",
     *          description="추천 근로자 간략한 정보 목록",
     *          @OA\Items (
     *              type="object",
     *              allOf={@OA\Schema(ref="#/components/schemas/recommended_worker_simple_info")}
     *          )
     *     )
     * )
     */
    function __construct(PageResult $result, WorkerRecommendation $recommendation) {
        $data = [];
        foreach($result->collection as $t) $data[] = RecommendedWorkerSimpleInfo::toArray($t, $recommendation);
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
