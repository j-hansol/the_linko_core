<?php

namespace App\Http\JsonResponses\V2\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

/**
 * 평가정보 목록
 * @OA\Schema(
 *     schema="eval_infos",
 *     description="평가정보 목록",
 *     @OA\Property(
 *          property="items",
 *          type="array",
 *          @OA\Items(ref="#/components/schemas/eval_info_include_item")
 *     )
 * )
 */
class EvalInfos extends PageResponse {
    function __construct(PageResult $result) {
        $data = [];
        foreach ($result->collection as $t) $data[] = $t->toArrayIncludeItems();
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
