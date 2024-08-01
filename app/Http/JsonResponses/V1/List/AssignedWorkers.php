<?php

namespace App\Http\JsonResponses\V1\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

/**
 * 배정 근로자 정보를 출력한다.
 * @OA\Schema(
 *     schema="assigned_workers",
 *     title="배정 근로자 목록",
 *     @OA\Property (
 *         property="items",
 *         type="array",
 *         @OA\Items (ref="#/components/schemas/assigned_worker")
 *     )
 * )
 */
class AssignedWorkers extends PageResponse {
    function __construct(PageResult $result) {
        $data = [];
        foreach ($result->collection as $t) $data[] = $t->toInfoArray();
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
