<?php

namespace App\Http\JsonResponses\V1\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

/**
 * 입국일정 정보 목록으로 응답한다.
 * @OA\Schema (
 *     schema="entry_infos",
 *     title="입국일정 정보 목록",
 *     @OA\Property (
 *          property="items",
 *          type="array",
 *          @OA\Items (ref="#/components/schemas/entry_info")
 *     )
 * )
 */
class EntryInfos extends PageResponse {
    function __construct(PageResult $result) {
        $data = [];
        foreach ($result->collection as $t) $data[] = $t->toArray();
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
