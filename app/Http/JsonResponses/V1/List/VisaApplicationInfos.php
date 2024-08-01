<?php
namespace App\Http\JsonResponses\V1\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema (
 *     schema="visa_info_list",
 *     title="비자정보 목록",
 *     @OA\Property(
 *          property="items",
 *          type="array",
 *          @OA\Items(
 *              type="object",
 *              ref="#/components/schemas/visa_info"
 *          )
 *     )
 * )
 */
class VisaApplicationInfos extends PageResponse {
    function __construct(PageResult $result, string $api_version = 'v1', bool $include_consulting_attorney = false) {
        $data = [];
        foreach ($result->collection as $t) $data[] = $t->toInfoArray($api_version, $include_consulting_attorney);
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
