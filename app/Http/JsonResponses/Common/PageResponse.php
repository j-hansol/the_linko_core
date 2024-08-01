<?php

namespace App\Http\JsonResponses\Common;

use App\Http\QueryParams\ListQueryParam;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema (
 *     schema="page_info",
 *     title="페이지정보",
 *     @OA\Property (
 *          property="total_items",
 *          type="integer",
 *          description="총 검색 자료 수"
 *     ),
 *     @OA\Property (
 *          property="total_page",
 *          type="integer",
 *          description="총 검색 페이지 수"
 *     ),
 *     @OA\Property (
 *          property="page",
 *          type="integer",
 *          description="페이지 번호"
 *     ),
 *     @OA\Property (
 *          property="page_per_items",
 *          type="integer",
 *          description="페이지당 항목 수"
 *     ),
 *     @OA\Property (
 *          property="item_count",
 *          type="integer",
 *          description="실재 검색된 항목 수"
 *     )
 * )
 */
class PageResponse extends JsonResponse {
    function __construct(array $arr, ListQueryParam $param, ?int $total = 0, ?int $total_page = 0) {
        parent::__construct([
            'message' => __('api.r200'),
            'total_items' => $total,
            'total_page' => $total_page,
            'page' => $param->page,
            'page_per_items' => $param->page_per_items,
            'item_count' => count($arr),
            'items' => $arr
        ]);
    }
}
