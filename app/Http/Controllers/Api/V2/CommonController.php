<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\JsonResponses\Common\Message;
use App\Http\JsonResponses\V2\List\Countries;
use App\Http\JsonResponses\V2\List\EvalInfos;
use App\Http\JsonResponses\V2\List\OccupationalGroups;
use App\Http\JsonResponses\V2\List\VisaDocumentTypes;
use App\Http\QueryParams\EvalTargetQueryParam;
use App\Http\QueryParams\ListQueryParam;
use App\Lib\PageResult;
use App\Models\Country;
use App\Models\EvalInfo;
use App\Models\OccupationalGroup;
use App\Models\VisaApplication;
use App\Models\VisaDocumentType;
use App\Services\Common\HttpException;
use App\Services\V2\CommonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="common",
 *     description="공용 API 인증 불필요"
 * )
 */
class CommonController extends Controller
{
    /**
     * 국가정보 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get (
     *     path="/common/listCountry",
     *     tags={"common"},
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
     *                 @OA\Schema (ref="#/components/schemas/country_list")
     *             }
     *         )
     *     ),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listCountry(ListQueryParam $param): JsonResponse {
        try {
            $service = CommonService::getInstance();
            return new Countries(new PageResult($service->listCountry($param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 요청한 국가정보를 리턴한다.
     * @param Country $id
     * @return JsonResponse
     *
     * @OA\Get (
     *     path="/common/getCountry/{id}",
     *     tags={"common"},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/country")
     *             }
     *         )
     *     ),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getCountry(Country $id): JsonResponse {
        return $id->response();
    }

    /**
     * 직업군정보 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get (
     *     path="/common/listOccupationalGroup",
     *     tags={"common"},
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
     *                 @OA\Schema (ref="#/components/schemas/occupational_group_list")
     *             }
     *         )
     *     ),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listOccupationalGroup(ListQueryParam $param): JsonResponse {
        try {
            $service = CommonService::getInstance();
            return new OccupationalGroups(new PageResult($service->listOccupationalGroup($param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 직업군 정보를 리턴한다.
     * @param OccupationalGroup $id
     * @return JsonResponse
     * @OA\Get (
     *     path="/common/getOccupationalGroup/{id}",
     *     tags={"common"},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/occupational_group")
     *             }
     *         )
     *     ),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getOccupationalGroup(OccupationalGroup $id): JsonResponse {
        return $id->response();
    }

    /**
     * 비자발급시 필요한 문서 유형 목록을 출력한다.
     * @param Request $request
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get (
     *     path="/common/listVisaDocumentType",
     *     tags={"common"},
     *     @OA\Parameter ( ref="#/components/parameters/visa_id"),
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
     *                 @OA\Schema (ref="#/components/schemas/visa_document_types")
     *             }
     *         )
     *     ),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listVisaDocumentType(Request $request, ListQueryParam $param) : JsonResponse {
        try {
            $service = CommonService::getInstance();
            $visa_id = $request->get('visa_id');
            $visa = $visa_id ? VisaApplication::findMe($visa_id) : null;
            return new VisaDocumentTypes(new PageResult($service->listVisaDocumentType($param, $visa), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급시 필요한 지정 문서유형 정보를 출력한다.
     * @param VisaDocumentType $id
     * @return JsonResponse
     * @OA\Get (
     *     path="/common/getVisaDocumentType/{id}",
     *     tags={"common"},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/visa_document_type")
     *             }
     *         )
     *     ),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getVisaDocumentType(VisaDocumentType $id) : JsonResponse {
        return $id->response();
    }

    /**
     * 지정 가능한 평가 설문 목록을 출력한다.
     * @param ListQueryParam $param
     * @param EvalTargetQueryParam $target)
     *     * @return JsonResponse
     * @OA\Get (
     *     path="/common/listEvalInfo",
     *     tags={"common"},
     *     @OA\Parameter ( ref="#/components/parameters/target"),
     *     @OA\Parameter ( ref="#/components/parameters/active"),
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
     *                 @OA\Schema (ref="#/components/schemas/eval_infos")
     *             }
     *         )
     *     ),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listEvalInfo(ListQueryParam $param, EvalTargetQueryParam $target) : JsonResponse {
        try {
            $service = CommonService::getInstance();
            return new EvalInfos(new PageResult($service->listEvalInfo($param, $target), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 설문정보 일체를 출력한다.
     * @param EvalInfo $id
     * @return JsonResponse
     * @OA\Get (
     *     path="/common/getEvaluation/{id}",
     *     tags={"common"},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/eval_info_include_item")
     *             }
     *         )
     *     ),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getEvaluation(EvalInfo $id) : JsonResponse {
        return $id->responseWithItems();
    }
}
