<?php

namespace App\Http\JsonResponses\V2\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Http\JsonResponses\V2\Base\UserInfoResponse;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema (
 *     schema="user_info_list",
 *     title="회원정보 목록",
 *     @OA\Property (
 *          property="items",
 *          type="array",
 *          @OA\Items (ref="#/components/schemas/user_info")
 *     )
 * )
 */
class UserInfos extends PageResponse {
    function __construct(PageResult $result) {
        $data = [];
        foreach ($result->collection as $t) $data[] = UserInfoResponse::toArray($t);
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
