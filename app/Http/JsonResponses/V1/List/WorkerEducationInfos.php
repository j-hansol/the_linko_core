<?php

namespace App\Http\JsonResponses\V1\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Http\JsonResponses\V1\Base\WorkerEducationInfo;
use App\Http\QueryParams\ListQueryParam;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

class WorkerEducationInfos extends PageResponse {
    /**
     * @param PageResult $result
     * @OA\Schema(
     *      schema="worker_education_infos",
     *      title="근로자 학력정보 목록",
     *      @OA\Property(
     *           property="items",
     *           type="array",
     *           description="방문 학력정보 목록",
     *           @OA\Items(
     *               type="object",
     *               allOf={@OA\Schema(ref="#/components/schemas/worker_education_info")}
     *           )
     *      )
     * )
     */
    function __construct(PageResult $result) {
        $data = [];
        foreach ($result->collection as $t) $data[] = WorkerEducationInfo::toArray($t);
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
