<?php

namespace App\Http\Controllers\Api\V2;

use App\DTOs\Common\IdDto;
use App\DTOs\V2\RecommendedWorkerStatusDto;
use App\DTOs\V2\WorkerRecommendationDto;
use App\DTOs\V2\WorkerRecommendationForOperatorDto;
use App\DTOs\V2\WorkerRecommendationRequestStatusDto;
use App\DTOs\V2\WorkerRecommendationRequestDto;
use App\Http\Controllers\Controller;
use App\Http\JsonResponses\Common\ErrorMessage;
use App\Http\JsonResponses\Common\Message;
use App\Http\JsonResponses\V2\Base\RecommendedWorkerInfo;
use App\Http\JsonResponses\V2\Base\WorkerRecommendationInfo;
use App\Http\JsonResponses\V2\Base\WorkerRecommendationRequestInfo;
use App\Http\JsonResponses\V2\List\RecommendedWorkerSimpleInfos;
use App\Http\JsonResponses\V2\List\WorkerRecommendationInfos;
use App\Http\JsonResponses\V2\List\WorkerRecommendationRequestInfos;
use App\Http\QueryParams\ListQueryParam;
use App\Http\Requests\V2\RequestAddRecommendedWorkers;
use App\Http\Requests\V2\RequestDeleteRecommendedWorkers;
use App\Http\Requests\V2\RequestRecommendedWorkerStatus;
use App\Http\Requests\V2\RequestWorkerRecommendation;
use App\Http\Requests\V2\RequestWorkerRecommendationRequest;
use App\Http\Requests\V2\RequestWorkerRecommendationRequestForOperator;
use App\Http\Requests\V2\RequestWorkerRecommendationRequestStatus;
use App\Http\Requests\V2\RequestWorkerRecommendationStatus;
use App\Lib\PageResult;
use App\Models\RecommendedWorker;
use App\Models\WorkerRecommendation;
use App\Models\WorkerRecommendationRequest;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use App\Services\V2\WorkerRecommendationService;
use Illuminate\Http\JsonResponse;
use Exception;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="recommendation",
 *     description="근로자 추천"
 * )
 */
class WorkerRecommendationController extends Controller {
    /**
     * 공유 요청 목록을 출력한다.
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/recommendation/listRequest",
     *     tags={"recommendation"},
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
     *                 @OA\Schema (ref="#/components/schemas/worker_recommendation_request_infos")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listRequest(ListQueryParam $param) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            return new WorkerRecommendationRequestInfos(new PageResult($service->listRequest($param), $param));
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 회원 본인이 신청한 추천 요청 목록을 출력한다.
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/recommendation/listRequestForUser",
     *     tags={"recommendation"},
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
     *                 @OA\Schema (ref="#/components/schemas/worker_recommendation_request_infos")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listRequestForUser(ListQueryParam $param) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            return new WorkerRecommendationRequestInfos(new PageResult($service->listRequestForUser($param), $param));
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 회원 본인이 공유 요청을 등록한다.
     * @param RequestWorkerRecommendationRequest $request
     * @return JsonResponse
     * @OA\Post(
     *     path="/recommendation/addRequest",
     *     tags={"recommendation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/input_worker_recommendation_request")
     *         )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function addRequest(RequestWorkerRecommendationRequest $request) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            $dto = WorkerRecommendationRequestDto::createFromRequest($request);
            $service->addRequest($dto);
            return new Message();
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 지정 근로자 추천 요청정보를 출력한다.
     * @param WorkerRecommendationRequest $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/recommendation/getRequest/{id}",
     *     tags={"recommendation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/id"),
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getRequest(WorkerRecommendationRequest $id) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            return new WorkerRecommendationRequestInfo($service->getWorkerRecommendationRequest($id));
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 지정 근로자 추천 요청정보를 변경한다.
     * @param RequestWorkerRecommendationRequest $request
     * @param WorkerRecommendationRequest $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/recommendation/updateRequest/{id}",
     *     tags={"recommendation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/input_worker_recommendation_request")
     *         )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function updateRequest(
        RequestWorkerRecommendationRequest $request,
        WorkerRecommendationRequest $id) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            $dto = WorkerRecommendationRequestDto::createFromRequest($request);
            $service->updateWorkerRecommendationRequest($dto, $id);
            return new Message();
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 지정 근로자 추천 요청정보를 삭제한다.
     * @param WorkerRecommendationRequest $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/recommendation/deleteRequest/{id}",
     *     tags={"recommendation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function deleteRequest(WorkerRecommendationRequest $id) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            $service->deleteWorkerRecommendationRequest($id);
            return new Message();
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 지정 요청 정보에 대한 승인 상태를 설정한다.
     * @param RequestWorkerRecommendationRequestStatus $request
     * @param WorkerRecommendationRequest $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/recommendation/setRequestStatus/{id}",
     *     tags={"recommendation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/set_worker_recommendation_request_status")
     *         )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function setRequestStatus(
        RequestWorkerRecommendationRequestStatus $request,
        WorkerRecommendationRequest $id) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            $dto = WorkerRecommendationRequestStatusDto::createFromRequest($request);
            $service->setRequestStatus($dto, $id);
            return new Message();
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 지정 요청정보에 대한 추천정보를 설정한다.
     * @param RequestWorkerRecommendation $request
     * @param WorkerRecommendationRequest $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/recommendation/setRecommendation/{id}",
     *     tags={"recommendation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/input_worker_recommendation")
     *         )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function setRecommendation(RequestWorkerRecommendation $request, WorkerRecommendationRequest $id) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            $dto = WorkerRecommendationDto::createFromRequest($request);
            $service->setRecommendation($dto, $id);
            return new Message();
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 근로자 추천 목록을 출력한다.
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/recommendation/listRecommendation",
     *     tags={"recommendation"},
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
     *                 @OA\Schema (ref="#/components/schemas/worker_recommendation_infos")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listRecommendation(ListQueryParam $param) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            return new WorkerRecommendationInfos(new PageResult($service->listRecommendation($param), $param));
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 회원 본인에게 추천하는 추천 정보 목록을 출력한다.
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/recommendation/listRecommendationForUser",
     *     tags={"recommendation"},
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
     *                 @OA\Schema (ref="#/components/schemas/worker_recommendation_infos")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     *  )
     */
    public function listRecommendationForUser(ListQueryParam $param) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            return new WorkerRecommendationInfos(new PageResult($service->listRecommendationForUser($param), $param));
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 관리자에 의해 요청정보와 추천정보를 함께 등록한다.
     * @param RequestWorkerRecommendationRequestForOperator $request
     * @return JsonResponse
     * @OA\Post(
     *     path="/recommendation/addRecommendation",
     *     tags={"recommendation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/input_worker_recommendation_request_for_operator")
     *         )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function addRecommendation(RequestWorkerRecommendationRequestForOperator $request) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            $dto = WorkerRecommendationForOperatorDto::createFromRequest($request);
            $service->addWorkerRecommendation($dto);
            return new Message();
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 지정 추천정보를 삭제한다.
     * @param WorkerRecommendation $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/recommendation/deleteRecommendation/{id}",
     *     tags={"recommendation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function deleteRecommendation(WorkerRecommendation $id) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            $service->deleteWorkerRecommendation($id);
            return new Message();
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 추천정보 상태를 설정한다.
     * @param RequestWorkerRecommendationStatus $request
     * @param WorkerRecommendation $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/recommendation/setRecommendationStatus/{id}",
     *     tags={"recommendation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/input_worker_recommendation_status")
     *         )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function setRecommendationStatus(
        RequestWorkerRecommendationStatus $request,
        WorkerRecommendation $id) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            $active = $request->boolean('active');
            $service->setWorkerRecommendationStatus($active, $id);
            return new Message();
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 지정 추천정보를 출력한다.
     * @param WorkerRecommendation $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/recommendation/getRecommendation/{id}",
     *     tags={"recommendation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/worker_recommendation_info")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getRecommendation(WorkerRecommendation $id) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            return new WorkerRecommendationInfo($service->getWorkerRecommendation($id), true);
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 요청정보에 대한 추천정보를 출력한다.
     * @param WorkerRecommendationRequest $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/recommendation/getRecommendationByRequest/{id}",
     *     tags={"recommendation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/worker_recommendation_info")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     *  )
     */
    public function getRecommendationByRequest(WorkerRecommendationRequest $id) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            $recommendation = $id->getRecommendation();
            return new WorkerRecommendationInfo($service->getWorkerRecommendation($recommendation), true);
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 지전 추천정보에 관련된 추천 근로자 목록을 출력한다.
     * @param ListQueryParam $param
     * @param WorkerRecommendation $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/recommendation/listRecommendedWorker/{id}",
     *     tags={"recommendation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
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
     *                 @OA\Schema (ref="#/components/schemas/recommended_worker_simple_infos")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listRecommendedWorker(ListQueryParam $param, WorkerRecommendation $id) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            return new RecommendedWorkerSimpleInfos(
                new PageResult($service->listRecommendedWorker($param, $id), $param),
                $id
            );
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 추츤 근로자 정보를 출력한다.
     * @param RecommendedWorker $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/recommendation/getWorkerInfo/{id}",
     *     tags={"recommendation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/recommended_worker_info")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getWorkerInfo(RecommendedWorker $id) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            $recommendation = WorkerRecommendation::findMe($id->worker_recommendation_id);
            return new RecommendedWorkerInfo($service->getRecommendedWorkerInfo($id), $recommendation);
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 지정 추천정보와 연관된 근로자를 추가한다.
     * @param RequestAddRecommendedWorkers $request
     * @param WorkerRecommendation $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/recommendation/addRecommendedWorkers/{id}",
     *     tags={"recommendation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/input_add_recommended_workers")
     *         )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function addRecommendedWorkers(RequestAddRecommendedWorkers $request, WorkerRecommendation $id) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            $dto = IdDto::createFromRequest($request, 'worker_ids');
            $service->addRecommendedWorkers($dto, $id);
            return new Message();
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 추천 근로자 정보를 삭제한다.
     * @param RequestDeleteRecommendedWorkers $request
     * @param WorkerRecommendation $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/recommendation/deleteRecommendedWorkers/{id}",
     *     tags={"recommendation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/input_delete_recommended_workers")
     *         )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function deleteRecommendedWorkers(RequestDeleteRecommendedWorkers $request, WorkerRecommendation $id) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            $dto = IdDto::createFromRequest($request, 'recommended_worker_ids');
            $service->deleteRecommendedWorkers($dto, $id);
            return new Message();
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }

    /**
     * 추천 근로자의 상태를 변경한다.
     * 이용 가능 대상 : 운영자
     * @param RequestRecommendedWorkerStatus $request
     * @param RecommendedWorker $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/recommendation/setRecommendedWorkerStatus/{id}",
     *     tags={"recommendation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/input_recommended_worker_status")
     *         )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function setRecommendedWorkerStatus(RequestRecommendedWorkerStatus $request, RecommendedWorker $id) : JsonResponse {
        try {
            $service = WorkerRecommendationService::getInstance();
            $dto = RecommendedWorkerStatusDto::createFromRequest($request);
            $service->setRecommendedWorkerStatus($dto, $id);;
            return new Message();
        } catch (HttpException $exception) {
            return new Message($exception->getCode());
        } catch (Exception $exception) {
            return new Message(500);
        }
    }
}
