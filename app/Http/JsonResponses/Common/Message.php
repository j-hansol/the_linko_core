<?php

namespace App\Http\JsonResponses\Common;

use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema (
 *     schema="api_message",
 *     title="Api 메시지",
 *     @OA\Property (
 *          property="message",
 *          type="string",
 *          description="에러 메시지"
 *     )
 * )
 * @OA\Response (
 *      response=200,
 *      description="요청성공",
 *      @OA\JsonContent (
 *          ref="#/components/schemas/api_message"
 *      )
 * )
 * @OA\Response (
 *      response=201,
 *      description="생성됨, 추가절차 필요",
 *      @OA\JsonContent (
 *          ref="#/components/schemas/api_message"
 *      )
 * )
 * @OA\Response(
 *     response="401",
 *     description="인증 실폐",
 *     @OA\JsonContent(
 *          ref="#/components/schemas/api_message"
 *     )
 * )
 * @OA\Response(
 *     response="403",
 *     description="권한 없음",
 *     @OA\JsonContent(
 *          ref="#/components/schemas/api_message"
 *     )
 * )
 * @OA\Response(
 *     response="404",
 *     description="자료 없음 (찾지 못함)",
 *     @OA\JsonContent(
 *          ref="#/components/schemas/api_message"
 *     )
 * )
 * @OA\Response(
 *      response=461,
 *      description="잘 못된 엑세스 토큰임",
 *      @OA\JsonContent(
 *          ref="#/components/schemas/api_message"
 *      )
 * )
 * @OA\Response(
 *     response="462",
 *     description="엑세스 토큰 만료됨",
 *     @OA\JsonContent(
 *          ref="#/components/schemas/api_message"
 *     )
 * )
 * @OA\Response(
 *      response="463",
 *      description="중복된 자료",
 *      @OA\JsonContent(
 *           ref="#/components/schemas/api_message"
 *      )
 *  )
 * @OA\Response(
 *     response="465",
 *     description="알 수 없는 단말기",
 *     @OA\JsonContent(
 *          ref="#/components/schemas/api_message"
 *     )
 * )
 * @OA\Response(
 *     response="468",
 *     description="범위 초과",
 *     @OA\JsonContent(
 *          ref="#/components/schemas/api_message"
 *     )
 * )
 * @OA\Response(
 *     response="500",
 *     description="서버 오류",
 *     @OA\JsonContent(
 *          ref="#/components/schemas/api_message"
 *     )
 * )
 */
class Message extends JsonResponse {
    function __construct(?int $code = 200) {
        parent::__construct(['message' => __('api.r' . $code)], $code);
    }
}
