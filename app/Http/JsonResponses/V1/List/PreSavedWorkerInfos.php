<?php

namespace App\Http\JsonResponses\V1\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

class PreSavedWorkerInfos extends PageResponse {
    /**
     * @param PageResult $result
     * @OA\Schema(
     *     schema="pre_save_worker_info_list",
     *     title="근로자 가족 목록",
     *     @OA\Property(
     *          property="items",
     *          type="array",
     *          description="임시 저장된 근로자 정보 목록",
     *          @OA\Items(
     *              type="object",
     *              allOf={@OA\Schema(ref="#/components/schemas/pre_save_worker_info")}
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
