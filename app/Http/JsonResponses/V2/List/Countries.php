<?php

namespace App\Http\JsonResponses\V2\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema (
 *     schema="country_list",
 *     title="국가정보 목록",
 *     @OA\Property (
 *          property="items",
 *          type="array",
 *          description="국가정보 목록",
 *          @OA\Items (
 *              type="object",
 *              allOf={@OA\Schema(ref="#/components/schemas/country")}
 *          )
 *     )
 * )
 */
class Countries extends PageResponse {
    function __construct(PageResult $result) {
        $data = [];
        foreach ($result->collection as $t) $data[] = $t->toArray();
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
