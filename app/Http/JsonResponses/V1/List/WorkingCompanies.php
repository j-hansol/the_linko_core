<?php

namespace App\Http\JsonResponses\V1\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema (
 *     schema="working_companies",
 *     title="근무 기업 채용 근로자 정보 목록",
 *     @OA\Property (
 *          property="items",
 *          type="array",
 *          @OA\Items(type="object", ref="#/components/schemas/working_company")
 *     )
 * )
 */
class WorkingCompanies extends PageResponse {
    public function __construct(PageResult $result) {
        $data = [];
        foreach ($result->collection as $t) $data[] = $t->toInfoArray();
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
