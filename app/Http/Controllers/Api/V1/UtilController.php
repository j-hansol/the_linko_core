<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\JsonResponses\Common\Data;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="util",
 *     description="보조 기능"
 * )
 */
class UtilController extends Controller {
    /**
     * UUID를 생성한다.
     * @return JsonResponse
     * @OA\Schema (
     *     schema="uuid",
     *     title="uuid",
     *     @OA\Property (
     *          property="uuid",
     *          type="string",
     *          description="uuid"
     *     )
     * )
     * @OA\Get (
     *     path="/util/genUUID",
     *     tags={"util"},
     *     @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/uuid")
     *              }
     *          )
     *      ),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function genUUID() : JsonResponse {
        return new Data(['uuid' => Str::uuid()]);
    }
}
