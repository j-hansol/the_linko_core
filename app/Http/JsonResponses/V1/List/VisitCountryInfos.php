<?php

namespace App\Http\JsonResponses\V1\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

class VisitCountryInfos extends PageResponse {
    /**
     * @param PageResult $result
     * @OA\Schema(
     *      schema="worker_visited_country_list",
     *      title="근로자 방문국가 목록",
     *      @OA\Property(
     *           property="items",
     *           type="array",
     *           description="방문 국가정보 목록",
     *           @OA\Items(
     *               type="object",
     *               allOf={@OA\Schema(ref="#/components/schemas/worker_visited_country")}
     *           )
     *      )
     * )
     */
    function __construct(PageResult $result) {
        $data = [];
        foreach ($result->collection as $t) $data[] = $t->toInfoArray();
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
