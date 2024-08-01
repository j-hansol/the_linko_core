<?php

namespace App\Http\JsonResponses\V1\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema (
 *     schema="worker_passports",
 *     title="업무 목록",
 *     @OA\Property (
 *          property="items",
 *          type="array",
 *          @OA\Items (ref="#/components/schemas/worker_passport")
 *     )
 * )
 */
class WorkerPassports extends PageResponse {
    /**
     * 근로자의 여권 목록을 JSON 형식으로 리턴한다.
     * @param PageResult $result
     */
    function __construct(PageResult $result) {
        $data = [];
        foreach ($result->collection as $t) $data[] = $t->toInfoArray('v1');
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
