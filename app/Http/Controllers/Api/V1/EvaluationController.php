<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\V1\EvaluationAnswerDto;
use App\Http\Controllers\Controller;
use App\Http\JsonResponses\Common\Data;
use App\Http\JsonResponses\Common\ErrorMessage;
use App\Http\JsonResponses\Common\Message;
use App\Http\JsonResponses\V1\List\Evauations;
use App\Http\QueryParams\ListQueryParam;
use App\Lib\PageResult;
use App\Models\AssignedWorker;
use App\Models\Evaluation;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use App\Services\V1\EvaluationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="evaluation",
 *     description="평가 기능"
 * )
 */
class EvaluationController extends Controller {
    /**
     * 본인이 평가결과 목록을 출력한다.
     * 이용 가능 대상 : 근로자 및 채용 기업
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/evaluation/listEvaluation",
     *     tags={"evaluation"},
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
     *                 @OA\Schema (ref="#/components/schemas/evaluations")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listEvaluation(ListQueryParam $param) : JsonResponse {
        try {
            $service = EvaluationService::getInstance();
            return new Evauations(new PageResult($service->listEvaluation($param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 평가결과를 등록한다.
     * 이용 가능 대상 : 근로자 및 채용 기업
     * @param Request $request
     * @param AssignedWorker $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/evaluation/addEvaluation/{id}",
     *     tags={"evaluation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *         @OA\MediaType (
     *             mediaType="application/json",
     *             @OA\Schema (ref="#/components/schemas/input_evaluation")
     *         )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     *  )
     */
    public function addEvaluation(Request $request, AssignedWorker $id) : JsonResponse {
        try {
            $service = EvaluationService::getInstance();
            $dto = EvaluationAnswerDto::createFromRequest($request);
            $service->addEvaluation($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 평가결과 정보를 출력한다.
     * 이용 가능 대상 : 근로자 및 채ㅣ용 기업
     * @param Evaluation $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/evaluation/getEvaluation/{id}",
     *     tags={"evaluation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/evaluation"),
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getEvaluation(Evaluation $id) : JsonResponse {
        try {
            $service = EvaluationService::getInstance();
            return new Data($service->getEvaluation($id)->toInfoArray());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 평가결과 등록자가 평가결과를 변경한다.
     * 이용 가능 대상 : 근로자 및 채용 기업
     * @param Request $request
     * @param Evaluation $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/evaluation/updateEvaluation/{id}",
     *     tags={"evaluation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *         @OA\MediaType (
     *             mediaType="application/json",
     *             @OA\Schema (ref="#/components/schemas/input_evaluation")
     *         )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function updateEvaluation(Request $request, Evaluation $id) : JsonResponse {
        try {
            $service = EvaluationService::getInstance();
            $dto = EvaluationAnswerDto::createFromRequest($request);
            $service->updateEvaluation($dto, $id);
            return new Message();
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 평가결과 등록자가 평가결과를 삭제한다.
     * 이용 가능 대상 : 근로자 및 채용 기업
     * @param Evaluation $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/evaluation/deleteEvaluation/{id}",
     *     tags={"evaluation"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function deleteEvaluation(Evaluation $id) : JsonResponse {
        try {
            $service = EvaluationService::getInstance();
            $service->deleteEvaluation($id);
            return new Message();
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }
}
