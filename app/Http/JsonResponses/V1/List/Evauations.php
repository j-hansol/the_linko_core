<?php

namespace App\Http\JsonResponses\V1\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="evaluations",
 *     description="평가 목록",
 *     @OA\Property(
 *          property="items",
 *          type="array",
 *          @OA\Items(ref="#/components/schemas/evaluation")
 *     )
 * )
 */
class Evauations extends PageResponse {
    function __construct(PageResult $result) {
        $data = [];
        foreach ($result->collection as $t) $data[] = $t->toInfoArray();
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
