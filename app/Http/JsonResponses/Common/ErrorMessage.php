<?php

namespace App\Http\JsonResponses\Common;

use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema (
 *     schema="api_error_message",
 *     title="Api 오류 메시지",
 *     @OA\Property (
 *          property="message",
 *          type="string",
 *          description="에러 메시지"
 *      ),
 *      @OA\Property (
 *          property="errors",
 *          type="array",
 *          @OA\Items (
 *              type="object",
 *              description="오류 필드 또는 항목을 속성으로 한 메시지 객체"
 *          )
 *      )
 * )
 * @OA\Response(
 *    response="400",
 *    description="잘 못된 요청",
 *    @OA\JsonContent(
 *         allOf={@OA\Schema (ref="#/components/schemas/api_error_message")}
 *    )
 * )
 *
 * @OA\Response(
 *     response="406",
 *     description="수용할 수 없음 (처리 거부됨)",
 *     @OA\JsonContent(
 *         allOf={@OA\Schema (ref="#/components/schemas/api_error_message")}
 *     )
 * )
 */
class ErrorMessage extends JsonResponse {
    function __construct(array $data, int $code = 400) {
        parent::__construct(['message' => __('api.r' . $code), 'errors' => $data], 400);
    }
}
