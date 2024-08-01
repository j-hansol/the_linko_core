<?php

namespace App\Http\JsonResponses\V1\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Http\JsonResponses\V1\Base\WorkerExperienceInfo;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema (
 *     schema="worker_experience_infos",
 *     title="근로자 경력정보 목록",
 *     @OA\Property (
 *          property="items",
 *          type="array",
 *          @OA\Items (ref="#/components/schemas/worker_experience_info")
 *     )
 * )
 */
class WorkerExperienceInfos extends PageResponse {
    function __construct(PageResult $result) {
        $data = [];
        foreach($result->collection as $info) $data[] = WorkerExperienceInfo::toArray($info);
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
