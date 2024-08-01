<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\V1\ActionPointDto;
use App\DTOs\V1\TaskDto;
use App\Http\Controllers\Controller;
use App\Http\JsonResponses\Common\Data;
use App\Http\JsonResponses\Common\Message;
use App\Http\JsonResponses\V1\List\ActionPoints;
use App\Http\JsonResponses\V1\List\AssignedWorkers;
use App\Http\JsonResponses\V1\List\Tasks;
use App\Http\QueryParams\ListQueryParam;
use App\Http\Requests\V1\RequestActionPoint;
use App\Http\Requests\V1\RequestTask;
use App\Lib\PageResult;
use App\Models\ActionPoint;
use App\Models\AssignedWorker;
use App\Models\Task;
use App\Services\Common\HttpException;
use App\Services\V1\CompanyService;
use Exception;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="company",
 *     description="채용 기업관련 기능"
 * )
 */
class CompanyController extends Controller {
    /**
     * 채용 기업이 등록한 업무 리스트 목록을 출력한다.
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/company/listTask",
     *     tags={"company"},
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
     *                 @OA\Schema (ref="#/components/schemas/tasks")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listTask(ListQueryParam $param) : JsonResponse {
        try {
            $service = CompanyService::getInstance();
            return new Tasks(new PageResult($service->listTask($param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 채용 기업의 새로운 업무를 등록한다.
     * @param RequestTask $request
     * @return JsonResponse
     * @OA\Post(
     *     path="/company/addTask",
     *     tags={"company"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_task")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function addTask(RequestTask $request) : JsonResponse {
        try {
            $service = CompanyService::getInstance();
            $service->addTask(TaskDto::createFromRequest($request));
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 채용 기업의 업무 정보를 출력한다.
     * @param Task $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/company/getTask/{id}",
     *     tags={"company"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (ref="#/components/schemas/task")
     *     ),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getTask(Task $id) : JsonResponse {
        try {
            $service = CompanyService::getInstance();
            return new Data($service->getTask($id)->toInfoArray());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 동영상 파일을 출력한다.
     * @param Task $id
     * @return mixed
     * @OA\Get (
     *     path="/company/showTaskMovie/{id}",
     *     tags={"company"},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Parameter (ref="#/components/parameters/_token"),
     *     @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\MediaType (mediaType="image/*",@OA\Schema (type="string",format="binary"))
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function showTaskMovie(Task $id) : mixed {
        try {
            $service = CompanyService::getInstance();
            return $service->showTaskMovie($id);
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 업무정보를 변경한다.
     * @param RequestTask $request
     * @param Task $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/updateTask/updateTask/{id}",
     *     tags={"company"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_task")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     *  )
     */
    public function updateTask(RequestTask $request, Task $id) : JsonResponse {
        try {
            $service = CompanyService::getInstance();
            $dto = TaskDto::createFromRequest($request);
            $service->updateTask($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 업무 정보를 삭제한다.
     * @param Task $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/company/deleteTask/{id}",
     *     tags={"company"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     *  )
     */
    public function deleteTask(Task $id) : JsonResponse {
        try {
            $service = CompanyService::getInstance();
            $service->deleteTask($id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 채용 기업의 기본 활동지점 목록을 출력한다.
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/company/listActionPoint",
     *     tags={"company"},
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
     *                 @OA\Schema (ref="#/components/schemas/action_points")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listActionPoint(ListQueryParam $param) : JsonResponse {
        try {
            $service = CompanyService::getInstance();
            return new ActionPoints(new PageResult($service->listActionPoint($param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 채용 기업의 새로운 기본 활동지점 정보를 등록한다.
     * @param RequestActionPoint $request
     * @return JsonResponse
     * @OA\Post(
     *     path="/company/addActionPoint",
     *     tags={"company"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody (
     *         @OA\MediaType (
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/input_action_point")
     *         )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     *  )
     */
    public function addActionPoint(RequestActionPoint $request) : JsonResponse {
        try {
            $service = CompanyService::getInstance();
            $service->addActionPoint(ActionPointDto::createFromRequest($request));
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 기본 활동지점 정보를 출력한다.
     * @param ActionPoint $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/company/getActionPoint/{id}",
     *     tags={"company"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (ref="#/components/schemas/action_point")
     *     ),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     *  )
     */
    public function getActionPoint(ActionPoint $id) : JsonResponse {
        try {
            $service = CompanyService::getInstance();
            return new Data($service->getActionPoint($id)->toInfoArray());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 활동지점 정보를 변경한다.
     * @param RequestActionPoint $request
     * @param ActionPoint $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/company/updateActionPoint/{id}",
     *     tags={"company"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *         @OA\MediaType (
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/input_action_point")
     *         )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function updateActionPoint(RequestActionPoint $request, ActionPoint $id) : JsonResponse {
        try {
            $service = CompanyService::getInstance();
            $dto = ActionPointDto::createFromRequest($request);
            $service->updateActionPoint($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 기본 활동지점 정보를 삭제한다.
     * @param ActionPoint $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/company/deleteActionPoint/{id}",
     *     tags={"company"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function deleteActionPoint(ActionPoint $id) : JsonResponse {
        try {
            $service = CompanyService::getInstance();
            $service->deleteActionPoint($id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 기업이 채용(중)한 근로자 목록을 출력한다.
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/company/listWorker",
     *     tags={"company"},
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
     *                 @OA\Schema (ref="#/components/schemas/tasks")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listWorker(ListQueryParam $param) : JsonResponse {
        try {
            $service = CompanyService::getInstance();
            return new AssignedWorkers(new PageResult($service->listWorker($param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 기업에 근무중인 근로자 활동지점 정보를 설정한다.
     * @param RequestActionPoint $request
     * @param AssignedWorker $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/company/setWorkerActionPoint/{id}",
     *     tags={"company"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *         @OA\MediaType (
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/input_action_point")
     *         )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function setWorkerActionPoint(RequestActionPoint $request, AssignedWorker $id) : JsonResponse {
        try {
            $service = CompanyService::getInstance();
            $dto = ActionPointDto::createFromRequest($request);
            $service->setWorkerActionPoint($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }
}
