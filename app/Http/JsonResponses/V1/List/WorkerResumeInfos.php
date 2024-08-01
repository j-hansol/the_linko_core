<?php

namespace App\Http\JsonResponses\V1\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Http\JsonResponses\V1\Base\WorkerResumeInfo;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema (
 *     schema="worker_resume_infos",
 *     title="근로자 이력서 목록",
 *     @OA\Property (
 *          property="items",
 *          type="array",
 *          @OA\Items (ref="#/components/schemas/worker_resume_info")
 *     )
 * )
 */
class WorkerResumeInfos extends PageResponse {
    function __construct(PageResult $result) {
        $data = [];
        foreach($result->collection as $info) $data[] = WorkerResumeInfo::toArray($info);
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
