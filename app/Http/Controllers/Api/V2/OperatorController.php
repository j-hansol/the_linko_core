<?php

namespace App\Http\Controllers\Api\V2;

use App\DTOs\V2\EvalInfoDto;
use App\DTOs\V2\EvalItemDto;
use App\DTOs\V2\OccupationalGroupDto;
use App\DTOs\V2\VisaDocumentTypeDto;
use App\Http\Controllers\Controller;
use App\Http\JsonResponses\Common\Data;
use App\Http\JsonResponses\Common\ErrorMessage;
use App\Http\JsonResponses\Common\Message;
use App\Http\JsonResponses\V2\List\UserInfos;
use App\Http\QueryParams\CountryParam;
use App\Http\QueryParams\ListQueryParam;
use App\Http\QueryParams\UserTypeParam;
use App\Http\Requests\V2\RequestAddVisaDocumentType;
use App\Http\Requests\V2\RequestEvalInfo;
use App\Http\Requests\V2\RequestEvalItem;
use App\Http\Requests\V2\RequestUpdateOccupationalGroup;
use App\Http\Requests\V2\RequestUpdateUserActiveState;
use App\Lib\PageResult;
use App\Models\EvalInfo;
use App\Models\EvalItem;
use App\Models\OccupationalGroup;
use App\Models\User;
use App\Models\VisaDocumentType;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use App\Services\V2\OperatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="operator",
 *     description="관리자 전용 기능"
 * )
 */
class OperatorController extends Controller {
    /**
     * 관리자가 다른 회원 계정으로 전환한다.
     * 이용 대상 : 운영 실무자
     * @param User $id
     * @return JsonResponse
     *
     * @OA\Get (
     *     path="/operator/switchUser/{id}",
     *     tags={"operator"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function switchUser(User $id) : JsonResponse {
        try {
            $service = OperatorService::getInstance();
            $service->switchUser($id);
            return new Message();
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 계정전환을 해제한다.
     * 이용 대상 : 운영 실무자
     * @return JsonResponse
     * @OA\Get (
     *     path="/operator/exitSwitchedUser",
     *     tags={"operator"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function exitSwitchedUser() : JsonResponse {
        try {
            $service = OperatorService::getInstance();
            $service->exitSwitchedUser();
            return new Message();
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 직업군 정보를 갱신한다.
     * 이용 대상 : 운영 실무자
     * @param RequestUpdateOccupationalGroup $request
     * @param OccupationalGroup $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/operator/updateOccupationalGroup/{id}",
     *     tags={"operator"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter(ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (ref="#/components/schemas/editable_occupational_group")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function updateOccupationalGroup(RequestUpdateOccupationalGroup $request, OccupationalGroup $id) : JsonResponse {
        try {
            $service = OperatorService::getInstance();
            $dto = OccupationalGroupDto::createFromRequest($request);
            $service->updateOccupationalGroup($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 회원 유형의 계정 목록을 출력한다.
     * @param ListQueryParam $param
     * @param UserTypeParam $type_param
     * @param CountryParam $country_param
     * @return JsonResponse
     * @OA\Get (
     *     path="/operator/listUser",
     *     tags={"operator"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/country_id"),
     *     @OA\Parameter ( ref="#/components/parameters/type"),
     *     @OA\Parameter ( ref="#/components/parameters/filter"),
     *     @OA\Parameter ( ref="#/components/parameters/op"),
     *     @OA\Parameter ( ref="#/components/parameters/keyword"),
     *     @OA\Parameter ( ref="#/components/parameters/page"),
     *     @OA\Parameter ( ref="#/components/parameters/page_per_items"),
     *     @OA\Parameter ( ref="#/components/parameters/order"),
     *     @OA\Parameter ( ref="#/components/parameters/dir"),
     *     @OA\Parameter ( ref="#/components/parameters/active_range"),
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
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listUser(ListQueryParam $param, UserTypeParam $type_param, CountryParam $country_param) : JsonResponse {
        try {
            $service = OperatorService::getInstance();
            if(!$type_param->type) throw new HttpErrorsException(['type' => 'user type required.']);

            return new UserInfos(
                new PageResult(
                    $service->listByMemberType($param, $type_param->type, $country_param), $param
                )
            );
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            // return new Message(500);
            return new Data([
                'message' => $e->getMessage(),
                'err_message' => $e->getMessage(),
                'errors' => $e->getTrace(),
            ], 500);
        }
    }

    /**
     * 개정 활성화 여부를 설정한다.
     * 이용 대상 : 운영 실무자
     * @param RequestUpdateUserActiveState $request
     * @param User $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/operator/updateUserActiveState/{id}",
     *     tags={"operator"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (ref="#/components/schemas/active_state")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function updateUserActiveState(RequestUpdateUserActiveState $request, User $id) : JsonResponse {
        try {
            $service = OperatorService::getInstance();
            $service->updateActivate($request->boolean('state'), $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 회원 유형을 변경한다.
     * 이용 대상 : 운영 실무자
     * @param Request $request
     * @param User $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/operator/updateUserType/{id}",
     *     tags={"operator"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (ref="#/components/schemas/user_types")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function updateUserType(Request $request, User $id) : JsonResponse {
        try {
            $service = OperatorService::getInstance();
            $types = explode(',', $request->input('types'));
            $service->updateUserType($types, $id);
            return new Message();
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 사증발급시 필요한 문서 유형을 등록한다.
     * 이용 대상 : 운영 실무자
     * @param RequestAddVisaDocumentType $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/operator/addVisaDocumentType",
     *     tags={"operator"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/updatable_visa_document_type")
     *          )
     *     ),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function addVisaDocumentType(RequestAddVisaDocumentType $request) : JsonResponse {
        try {
            $service = OperatorService::getInstance();
            $dto = VisaDocumentTypeDto::createFromRequest($request);
            $service->addVisaDocumentType($dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 사증발급시 필요한 문서 유형을 변경한다.
     * 이용 대상 : 운영 실무자
     * @param RequestAddVisaDocumentType $request
     * @param VisaDocumentType $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/operator/updateVisaDocumentType/{id}",
     *     tags={"operator"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/updatable_visa_document_type")
     *          )
     *     ),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function updateVisaDocumentType(RequestAddVisaDocumentType $request, VisaDocumentType $id) : JsonResponse {
        try {
            $service = OperatorService::getInstance();
            $dto = VisaDocumentTypeDto::createFromRequest($request);
            $service->updateVisaDocumentType($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 문서 유형을 삭제한다. 만일 해당 문서 유형의 문사가 존재한다면 삭제하지 않는다.
     * 이용 대상 : 운영 실무자
     * @param VisaDocumentType $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/operator/deleteVisaDocumentType/{id}",
     *     tags={"operator"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=406, ref="#/components/responses/406"),
     *      @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function deleteVisaDocumentType(VisaDocumentType $id) : JsonResponse {
        try {
            $service = OperatorService::getInstance();
            $service->deleteVisaDocumentType($id);
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
     * 평가 설문을 등록한다.
     * @param RequestEvalInfo $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/operator/addEvalInfo",
     *     tags={"operator"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/eval_info")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function addEvalInfo(RequestEvalInfo $request) : JsonResponse {
        try {
            $service = OperatorService::getInstance();
            $dto = EvalInfoDto::createFromRequest($request);
            $service->addEvalInfo($dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 평가 설문을 수정한다.
     * @param RequestEvalInfo $request
     * @param EvalInfo $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/operator/updateEvalInfo/{id}",
     *     tags={"operator"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/eval_info")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function updateEvalInfo(RequestEvalInfo $request, EvalInfo $id) : JsonResponse {
        try {
            $service = OperatorService::getInstance();
            $dto = EvalInfoDto::createFromRequest($request);
            $service->updateEvalInfo($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 평가 설문을 삭제한다.
     * @param EvalInfo $id
     * @return JsonResponse
     * @OA\Get (
     *     path="/operator/deleteEvalInfo/{id}",
     *     tags={"operator"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function deleteEvalInfo(EvalInfo $id) : JsonResponse {
        try {
            $service = OperatorService::getInstance();
            $service->deleteEvalInfo($id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 평가의 지정 설문에 설문 항목 등혹한다.
     * @param RequestEvalItem $request
     * @param EvalInfo $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/operator/addEvalItem/{id}",
     *     tags={"operator"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_eval_item")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function addEvalItem(RequestEvalItem $request, EvalInfo $id) : JsonResponse {
        try {
            $service = OperatorService::getInstance();
            $dto = EvalItemDto::createFromRequest($request);
            $service->addEvalItem($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 평가의 지정 설문 항목을 수정한다.
     * @param RequestEvalItem $request
     * @param EvalItem $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/operator/updateEvalItem/{id}",
     *     tags={"operator"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_eval_item")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function updateEvalItem(RequestEvalItem $request, EvalItem $id) : JsonResponse {
        try {
            $service = OperatorService::getInstance();
            $dto = EvalItemDto::createFromRequest($request);
            $service->updateEvalItem($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 근로자 평가 설문 항목을 삭제한다.
     * @param EvalItem $id
     * @return JsonResponse
     * @OA\Get (
     *     path="/operator/deleteEvalItem/{id}",
     *     tags={"operator"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function deleteEvalItem(EvalItem $id) : JsonResponse {
        try {
            $service = OperatorService::getInstance();
            $service->deleteEvalItem($id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }
}
