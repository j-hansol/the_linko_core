<?php

namespace App\Http\JsonResponses\V1\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

class WorkerFamilyList extends PageResponse {
    /**
     * @param PageResult $result
     * @OA\Schema(
     *     schema="worker_family_list",
     *     title="근로자 가족 목록",
     *     @OA\Property(
     *          property="items",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              ref="#/components/schemas/worker_family"
     *          )
     *     )
     * )
     */
    function __construct(PageResult $result) {
        $data = [];
        foreach ($result->collection as $t) $data[] = $t->toInfoArray();
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
