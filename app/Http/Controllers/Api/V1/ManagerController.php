<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\V1\ManagerOperatorDto;
use App\Http\Controllers\Controller;
use App\Http\JsonResponses\Common\ErrorMessage;
use App\Http\JsonResponses\Common\Message;
use App\Http\JsonResponses\V1\List\UserInfos;
use App\Http\QueryParams\ListQueryParam;
use App\Http\Requests\V1\RequestJoinManagerOperator;
use App\Lib\PageResult;
use App\Models\User;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use App\Services\V1\ManagerOperatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="manager",
 *     description="국내 및 해외 관리기관의 실무자 관리기능"
 * )
 */
class ManagerController extends Controller {
    /**
     * 실무자 목록을 출력한다.
     * @param Request $request
     * @return JsonResponse
     * @OA\Get(
     *     path="/manager/listOperator",
     *     tags={"manager"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
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
     * )
     */
    public function listOperator(ListQueryParam $param) : JsonResponse {
        try {
            $service = ManagerOperatorService::getInstance();
            return new UserInfos(new PageResult($service->listOperator($param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 실무자 계정을 생성한다.
     * @param RequestJoinManagerOperator $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/manager/joinOperator",
     *     tags={"manager"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (
     *                  allOf={
     *                      @OA\Schema (ref="#/components/schemas/join_password"),
     *                      @OA\Schema (ref="#/components/schemas/join_manager_opwerator"),
     *                  },
     *                  required={"password"}
     *              )
     *          )
     *     ),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function joinOperator(RequestJoinManagerOperator $request) : JsonResponse {
        try {
            $service = ManagerOperatorService::getInstance();
            $dto = ManagerOperatorDto::createFromRequest($request);
            $service->joinOperator($dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 실무자 계정을 활성화 또는 비활성화한다.
     * @param Request $request
     * @param User $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/manager/setActiveOperator/{id}",
     *     tags={"manager"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (
     *                  @OA\Property(
     *                      property="active",
     *                      type="integer",
     *                      enum={"0","1"},
     *                      description="활성화 여부"
     *                  ),
     *                  required={"active"}
     *              )
     *          )
     *     ),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function setActiveOperator(Request $request, User $id) : JsonResponse {
        try {
            $service = ManagerOperatorService::getInstance();
            $validator = Validator::make($request->input(), ['active' => ['required', 'boolean']]);
            if($validator->fails())
                throw HttpErrorsException::getInstance($validator->getMessageBag()->toArray(), 400);
            $service->setActiveOperator($id, $request->boolean('active'));
            return new Message();
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 관리기관 실무자에서 역활을 취소한다. 역활 취소 시 실무자 유형을 제거한다.
     * @param User $id
     * @return JsonResponse
     * @OA\Get (
     *     path="/manager/cancelOperator/{id}",
     *     tags={"manager"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function cancelOperator(User $id) : JsonResponse {
        try {
            $service = ManagerOperatorService::getInstance();
            $service->cancelOperator($id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }
}
