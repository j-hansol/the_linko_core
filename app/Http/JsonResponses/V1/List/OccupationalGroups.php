<?php

namespace App\Http\JsonResponses\V1\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema (
 *     schema="occupational_group_list",
 *     title="국가정보 목록",
 *     @OA\Property (
 *          property="items",
 *          type="array",
 *          description="직업군정보 목록",
 *          @OA\Items (
 *              type="object",
 *              allOf={@OA\Schema(ref="#/components/schemas/occupational_group")}
 *          )
 *     )
 * )
 */
class OccupationalGroups extends PageResponse {
    function __construct(PageResult $result) {
        $data = [];
        foreach ($result->collection as $t) $data[] = $t->toArray();
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
