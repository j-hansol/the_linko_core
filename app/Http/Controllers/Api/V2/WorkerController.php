<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\JsonResponses\Common\Message;
use App\Http\JsonResponses\V2\List\UserInfos;
use App\Http\QueryParams\ListQueryParam;
use App\Lib\PageResult;
use App\Services\V2\WorkerManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="worker",
 *     description="근로자관련 기능"
 * )
 */
class WorkerController extends Controller {
    /**
     * 근로자 목록을 출력한다.
     * 이용 가능 대상 : 운영자
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/listWorkerForOperator",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/country_id"),
     *     @OA\Parameter ( ref="#/components/parameters/filter"),
     *     @OA\Parameter ( ref="#/components/parameters/op"),
     *     @OA\Parameter ( ref="#/components/parameters/keyword"),
     *     @OA\Parameter ( ref="#/components/parameters/page"),
     *     @OA\Parameter ( ref="#/components/parameters/page_per_items"),
     *     @OA\Parameter ( ref="#/components/parameters/order"),
     *     @OA\Parameter ( ref="#/components/parameters/dir"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/page_info"),
     *                 @OA\Schema (ref="#/components/schemas/user_info_list")
     *             }
     *         )
     *     ),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     *  )
     */
    public function listWorkerForOperator(ListQueryParam $param) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new UserInfos(new PageResult($service->listWorkerForOperator($param), $param));
        } catch (Exception $exception) {
            return new Message(500);
        }
    }
}
