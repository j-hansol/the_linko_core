<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\V1\AssistanceDto;
use App\DTOs\V1\ContactDto;
use App\DTOs\V1\FamilyDetailDto;
use App\DTOs\V1\FundingDetailDto;
use App\DTOs\V1\IdsDto;
use App\DTOs\V1\InvitorDto;
use App\DTOs\V1\PassportDto;
use App\DTOs\V1\RequestVisaApplicationDto;
use App\DTOs\V1\VisaApplicationIssuedInfoDto;
use App\DTOs\V1\VisaApplicationJsonDto;
use App\DTOs\V1\VisaDocumentDto;
use App\DTOs\V1\VisaEducationDto;
use App\DTOs\V1\VisaEmploymentDto;
use App\DTOs\V1\VisaMessageDto;
use App\DTOs\V1\VisaPassportJsonDto;
use App\DTOs\V1\VisitDetailDto;
use App\DTOs\V1\WorkerAccountJsonDto;
use App\DTOs\V1\WorkerProfileDto;
use App\Http\Controllers\Controller;
use App\Http\JsonResponses\Common\Data;
use App\Http\JsonResponses\Common\ErrorMessage;
use App\Http\JsonResponses\Common\Message;
use App\Http\JsonResponses\V1\List\AvailableVisaApplicationStatus;
use App\Http\JsonResponses\V1\List\RequestConsultingPermissions;
use App\Http\JsonResponses\V1\List\VisaApplicationInfos;
use App\Http\QueryParams\ListQueryParam;
use App\Http\Requests\V1\RequestConsultingMessage;
use App\Http\Requests\V1\RequestSetAssistance;
use App\Http\Requests\V1\RequestSetContact;
use App\Http\Requests\V1\RequestSetEducation;
use App\Http\Requests\V1\RequestSetEmployment;
use App\Http\Requests\V1\RequestSetFamilyDetail;
use App\Http\Requests\V1\RequestSetFundingDetail;
use App\Http\Requests\V1\RequestSetInvitor;
use App\Http\Requests\V1\RequestSetPassport;
use App\Http\Requests\V1\RequestSetVisitDetail;
use App\Http\Requests\V1\RequestTargetAttorney;
use App\Http\Requests\V1\RequestUpdateProfile;
use App\Http\Requests\V1\RequestUpdateVisaStatus;
use App\Http\Requests\V1\RequestVisa;
use App\Http\Requests\V1\RequestVisaDocument;
use App\Http\Requests\V1\RequestVisaFamilyIds;
use App\Http\Requests\V1\RequestVisaVisitCountryIds;
use App\Lib\PageResult;
use App\Lib\VisaApplicationStatus;
use App\Models\User;
use App\Models\VisaApplication;
use App\Models\VisaDocument;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use App\Services\V1\VisaApplicationService;
use App\Services\V1\WorkerManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="visa",
 *     description="비자관련 업무"
 * )
 */
class VisaController extends Controller {
    /**
     * 비자발급 정보를 목록으로 출력한다.
     * 이용 가능 대상 : 해외 개인(근로자)
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/visa/listVisa",
     *     tags={"visa"},
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
     *                 @OA\Schema (ref="#/components/schemas/visa_info_list")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listVisa(ListQueryParam $param) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            return new VisaApplicationInfos(new PageResult($service->listVisaApplication($param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 관리중인 근로자의 비자발급 정보를 목록으로 출력한다.
     * 이용 가능 대상 : 해외 관리기관, 해외 관리기관 실무자
     * @param ListQueryParam $param
     * @param User $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/visa/listWorkerVisa/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
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
     *                 @OA\Schema (ref="#/components/schemas/visa_info_list")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listWorkerVisa(ListQueryParam $param, User $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            return new VisaApplicationInfos(new PageResult($service->listVisaApplication($param, $id), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 컨설팅 권한 요청 가능한 비자신청 목록을 출력한다.
     * 이용 가능 대상 : 운영 실무자, 행정사
     * @param ListQueryParam $param)
     * @return JsonResponse
     * @OA\Get(
     *     path="/visa/listConsultAbleVisa",
     *     tags={"visa"},
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
     *                 @OA\Schema (ref="#/components/schemas/visa_info_list")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listConsultAbleVisa(ListQueryParam $param) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            return new VisaApplicationInfos(new PageResult($service->listConsultAbleVisa($param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 행정사 본인이 컨설팅 중인 바자발급정보 목록을 출력한다.
     * 이용 가능 대상 : 행정사
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/visa/listConsultingVisa",
     *     tags={"visa"},
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
     *                 @OA\Schema (ref="#/components/schemas/visa_info_list")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listConsultingVisa(ListQueryParam $param) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            return new VisaApplicationInfos(new PageResult($service->listConsultingVisa($param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 행정사 본인이 컨설팅 완료된 비자발급 정보 목록을 출력한다.
     * 이용 가능 대상 : 행정사
     * @param Request $request
     * @return JsonResponse
     * @OA\Get(
     *     path="/visa/listConsultedVisa",
     *     tags={"visa"},
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
     *                 @OA\Schema (ref="#/components/schemas/visa_info_list")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listConsultedVisa(ListQueryParam $param) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            return new VisaApplicationInfos(new PageResult($service->listConsultingVisa($param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급 신청서를 작성한다.
     * 이용 가능 대상 : 해외 개인(근로자)
     * @param RequestVisa $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/visa/request",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/request_visa")
     *          )
     *     ),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/visa_master_info")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function request(RequestVisa $request) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = RequestVisaApplicationDto::createFromRequest($request);
            return new Data($service->requestVisaApplication($dto)?->toArray());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급 신청서를 작성한다.
     * 이용 가능 대상 : 관리 중인 해외 관리기관 및 실무자
     * @param RequestVisa $request
     * @param User $worker_id
     * @return JsonResponse
     * @OA\Post (
     *     path="/visa/requestForWorker/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/request_visa")
     *          )
     *     ),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/visa_master_info")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function requestForWorker(RequestVisa $request, User $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = RequestVisaApplicationDto::createFromRequest($request);
            return new Data($service->requestVisaApplication($dto, $id)?->toArray());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자정보를 출력한다.
     * 이용 가능 대상 : 운영 실무자, 바자 신청자(근로자), 신청자를 관리하는 기관 및 실무자, 컨설팅 중인 행정사, 비자발급 업무 담당 행정사
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/visa/get/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (ref="#/components/schemas/visa_info")
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function get(VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            return new Data(
                $service->getVisaApplication($id)->toInfoArray('v1', true));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급 정보를 수정한다.
     * 이용 가능 대상 : 관리 중인 해외 관리기관 및 실무자
     * @param RequestVisa $request
     * @param User $worker_id
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/update/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/request_visa")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function update(RequestVisa $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = RequestVisaApplicationDto::createFromRequest($request);
            $service->updateVisaApplication($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자, 컨설턴터, 발급업무 진행 행정사가 비자발급 신청의 상태를 변경한다.
     * 이용 가능 대상 : 해외 개인(근로자), 근로자를 관리중인 해외 기관 및 실무자, 컨설팅 또는 발급어부 진행 중인 행정사
     * @param RequestUpdateVisaStatus $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/updateStatus/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  allOf={
     *                      @OA\Schema(ref="#/components/schemas/vista_status"),
     *                      @OA\Schema(ref="#/components/schemas/input_visa_issue_info")
     *                  }
     *              )
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function updateStatus(RequestUpdateVisaStatus $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $status = $request->enum('status', VisaApplicationStatus::class);
            $dto = $status == VisaApplicationStatus::STATUS_ISSUE_COMPLETE ?
                VisaApplicationIssuedInfoDto::createFromRequest($request) : null;
            $service->updateVisaApplicationStatus($status, $id, $dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 기존 비자 상태정보를 바탕으로 다음 설정 가능한 상태정보(번호) 목록을 출력한다.
     * 이용 대상 : 근로자, 해외 관리기관, 행정사
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/visa/getAvailableStatus/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/available_visa_application_status")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getAvailableStatus(VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            return new AvailableVisaApplicationStatus($service->getAvailableVisaStatus($id));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급 신청정보를 취소한다. 삭제는 등록중인 것만 가능하다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/visa/delete/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function delete(VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $service->deleteVisaApplication($id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급시 사용할 프로필정보를 변경한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestUpdateProfile $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/updateProfile/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/update_visa_profile")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function updateProfile(RequestUpdateProfile $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = WorkerProfileDto::createFromRequest($request);
            $service->updateProfile($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급에 사용될 사진을 지정하거나 갱신한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param Request $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/setPhoto/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/image")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function setPhoto(Request $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $validator = Validator::make($request->allFiles(), ['image' => ['required', 'image']]);
            if($validator->fails()) return new Message(400);
            $file = $request->file('image');
            $service->setPhoto($file, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급에 사용된 사진을 출력한다.
     * 이용 가능 대상 : 모든 로그인 사용자
     * @param VisaApplication $id
     * @return mixed
     * @OA\Get(
     *     path="/visa/showPhoto/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Parameter (ref="#/components/parameters/_token"),
     *     @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\MediaType (
     *              mediaType="image/*",
     *              @OA\Schema (
     *                  type="string",
     *                  format="binary"
     *              )
     *          )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function showPhoto(VisaApplication $id) : mixed {
        try {
            $service = VisaApplicationService::getInstance();
            return $service->showPhoto($id);
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급 신청을 위한 여권정보를 등록하거나 변경한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestSetPassport $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/setPassport/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/updatable_visa_passport")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function setPassport(RequestSetPassport $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = PassportDto::createFromRequest($request);
            $service->setPassport($dto, $id);
            return new Message();
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message( 500);
        }
    }

    /**
     * 여권파일을 출력한다.
     * 이용 가능 대상 : 모든 로그인 사용자
     * @param VisaApplication $id
     * @return mixed
     * @OA\Get(
     *     path="/visa/showPassportFile/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Parameter (ref="#/components/parameters/_token"),
     *     @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\MediaType (
     *              mediaType="image/*",
     *              @OA\Schema (
     *                  type="string",
     *                  format="binary"
     *              )
     *          )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function showPassportFile(VisaApplication $id) : mixed {
        try {
            $service = VisaApplicationService::getInstance();
            return $service->showPassportFile($id);
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급에 사용할 연락처 정보를 등록하거나 변경한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestSetContact $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/setContact/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/updatable_visa_contact")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function setContact(RequestSetContact $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = ContactDto::createFromRequest($request);
            $service->setContact($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급시 사용할 혼인관계 및 가족사항 정보를 등록하거나 변경한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestSetFamilyDetail $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/setFamilyDetail/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/updatable_visa_family_detail")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function setFamilyDetail(RequestSetFamilyDetail $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = FamilyDetailDto::createFromRequest($request);
            $service->setFamilyDetail($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급시 사용될 할력정보를 등록하거나 변경한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestSetEducation $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/setEducation/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/updatable_visa_education")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function setEducation(RequestSetEducation $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = VisaEducationDto::createFromRequest($request);
            $service->setEducation($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급시 사용할 직업정보를 등록하거나 변경한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestSetEmployment $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/setEmployment/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/updatable_visa_employment")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function setEmployment(RequestSetEmployment $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = VisaEmploymentDto::createFromRequest($request);
            $service->setEmployment($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급시 사용할 방문상세정보를 등록하거나 변경한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestSetVisitDetail $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/setVisitDetail/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/updatable_visa_visit_detail")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function setVisitDetail(RequestSetVisitDetail $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = VisitDetailDto::createFromRequest($request);
            $service->setVisitDetail($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 방문정보에 최근 5년 이내 한국 방문 내역을 설정한다. setVisitDetail 기능 호출이 선행되어야 한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestVisaVisitCountryIds $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/setVisitKoreaHistory/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/ids")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function setVisitKoreaHistory(RequestVisaVisitCountryIds $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = IdsDto::createFromRequest($request);
            $service->setVisitDetailFieldIds($dto, $id, 'visit_korea_ids');
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
     * 방문정보에 최근 5년 이내 다른 나라 방문 내역을 설정한다. setVisitDetail 기능 호출이 선행되어야 한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestVisaVisitCountryIds $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/setVisitOtherCountryHistory/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/ids")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function setVisitOtherCountryHistory(RequestVisaVisitCountryIds $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = IdsDto::createFromRequest($request);
            $service->setVisitDetailFieldIds($dto, $id, 'visit_country_ids');
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
     * 방문정보에 현재 국내 거주 가적 내역을 설정한다. setVisitDetail 기능 호출이 선행되어야 한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestVisaFamilyIds $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/setStayFamilyInKorea/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/ids")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function setStayFamilyInKorea(RequestVisaFamilyIds $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = IdsDto::createFromRequest($request);
            $service->setVisitDetailFieldIds($dto, $id, 'stay_family_ids');
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
     * 방문정보에 동반 입국 가족 내역을 설정한다. setVisitDetail 기능 호출이 선행되어야 한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @OA\Post(
     *     path="/visa/setFamilyMember/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/ids")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     * @param RequestVisaFamilyIds $request
     * @param VisaApplication $id
     * @return JsonResponse
     */
    public function setFamilyMember(RequestVisaFamilyIds $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = IdsDto::createFromRequest($request);
            $service->setVisitDetailFieldIds($dto, $id, 'family_member_ids');
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
     * 비자발급시 사용될 초청자정보를 등록하거나 변경한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestSetInvitor $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/setInvitor/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/updatable_visa_invitor")
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
    public function setInvitor(RequestSetInvitor $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = InvitorDto::createFromRequest($request);
            $service->setInvitor($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급시 사용될 비용정보를 등록하거나 변경한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestSetFundingDetail $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/setFundingDetail/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/updatable_visa_funding_detail")
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
    public function setFundingDetail(RequestSetFundingDetail $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = FundingDetailDto::createFromRequest($request);
            $service->setFundingDetail($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급시 사용할 서류작성 도우미정보를 등록하거나 변경한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestSetAssistance $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/setAssistance/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/updatable_visa_assitance")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function setAssistance(RequestSetAssistance $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = AssistanceDto::createFromRequest($request);
            $service->setAssistance($dto, $id);
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
     * 비자발급에 필요한 문서를 등록한다. (비자정보 일련번호 필요)
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestVisaDocument $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/addVisaDocument/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/updatable_visa_document")
     *          )
     *     ),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/visa_document"),
     *             }
     *         )
     *     ),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function addVisaDocument(RequestVisaDocument $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = VisaDocumentDto::createFromRequest($request);
            $service->addDocument($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 등록된 문서를 변경한다. (문서 일련번호 필요)
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestVisaDocument $request
     * @param VisaDocument $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/updateVisaDocument/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/updatable_visa_document")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function updateVisaDocument(RequestVisaDocument $request, VisaDocument $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = VisaDocumentDto::createFromRequest($request);
            $service->updateDocument($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 등록된 비자발급에 필요한 문서 내용을 출력한다. (문서 일련번호 필요)
     * 이용 가능 대상 : 모든 로그인 사용자
     * @param VisaDocument $id
     * @return mixed
     * @OA\Get(
     *     path="/visa/showVisaDocument/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Parameter (ref="#/components/parameters/_token"),
     *     @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\MediaType (
     *              mediaType="application/*",
     *              @OA\Schema (
     *                  type="string",
     *                  format="binary"
     *              )
     *          )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function showVisaDocument(VisaDocument $id) : mixed {
        try {
            $service = VisaApplicationService::getInstance();
            return $service->showDocumentFile($id);
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 등록된 문서를 삭제한다. (문서일련번호 필요)
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param VisaDocument $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/visa/deleteVisaDocument/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function deleteVisaDocument(VisaDocument $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $service->deleteDocument($id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자, 근로자 관리(해외) 기관 및 기관의 실무자, 행정사가 비자발급관련 메시지를 전송한다.
     * 이용 대상 : 근로자, 해외 관리 기관 및 실무자, 행정사
     * @param RequestConsultingMessage $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/sendMessage/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/send_consulting_message_to_worker")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function sendMessage(RequestConsultingMessage $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = VisaMessageDto::createFromRequest($request);
            $service->sendMessage($dto, $id);
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
     * 지정 비자발급정보에 대한 컨설팅 행정사를 지정한다.
     * 이용 대상 : 운영 실무자
     * @param User $attorney_id
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/visa/setConsultingAttorney/{attorney_id}/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/attorney_id"),
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function setConsultingAttorney(User $attorney_id, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $service->setConsultingAttorney($attorney_id, $id);
            return new Message();
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 다수의 비자발급 정보에 대한 컨설팅 행정사를 지정한다.
     * 이용 대상 : 운영 실무자
     * @param RequestTargetAttorney $request
     * @param User $attorney_id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/setConsultingAttorneyMultiple/{attorney_id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/attorney_id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/visa_ids")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function setConsultingAttorneyMultiple(RequestTargetAttorney $request, User $attorney_id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $visa_ids = explode(',', $request->input('visa_ids'));
            $service->setConsultingAttorneyMultiple($visa_ids, $attorney_id);
            return new Message();
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 컨설팅 중인 바자발급정보 목록을 출력한다.
     * 이용 대상 : 운영 실무자
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/visa/listConsultingVisaForOperator",
     *     tags={"visa"},
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
     *                 @OA\Schema (ref="#/components/schemas/visa_info_list")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listConsultingVisaForOperator(ListQueryParam $param) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            return new VisaApplicationInfos(new PageResult($service->listConsultingVisaForOperator($param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 컨설팅 완료된 비자발급 정보 목록을 출력한다.
     * 이용 대상 : 운영 실무자
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/visa/listConsultedVisaForOperator",
     *     tags={"visa"},
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
     *                 @OA\Schema (ref="#/components/schemas/visa_info_list")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listConsultedVisaForOperator(ListQueryParam $param) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            return new VisaApplicationInfos(new PageResult($service->listConsultedVisaForOperator($param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 컨설팅 권한을 요청한다.
     * 이용 대상 : 행정사
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/visa/requestConsultingPermission/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function requestConsultingPermission(VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $service->requestConsultingPermission($id);
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
     * 지정 비자발급 정보에 대한 컨설팅 권한 요청 정보 목록을 출력한다.
     * 이용 대상 : 운영 실무자
     * @param ListQueryParam $param
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/visa/listRequestPermission/{id}",
     *     tags={"visa"},
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
     *                 @OA\Schema (ref="#/components/schemas/request_consulting_permissions")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listRequestPermission(ListQueryParam $param, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            return new RequestConsultingPermissions(new PageResult($service->listRequestPermission($id, $param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비자발급 업무를 배정한다.
     * @param User $attorney
     * @param VisaApplication $id
     * @return JsonResponse
     */
    public function assignIssueTask(User $attorney, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $service->assignIssueTask($id, $attorney);
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
     * 다수의 비자발급 정보를 지정 행정사에게 발급업무를 배정한다.
     * 이용 대상 : 운영 실무자
     * @param RequestTargetAttorney $request
     * @param User $attorney_id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/assignIssueTaskMultiple/{attorney_id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/attorney_id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/visa_ids")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function assignIssueTaskMultiple(RequestTargetAttorney $request, User $attorney_id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $visa_ids = explode(',', $request->input('visa_ids'));
            $service->assignIssueTaskMultiple($visa_ids, $attorney_id);
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
     * 전달된 JSON 데이터로부터 근로자 계정을 생성하고 비자 신청정보를 등록한다. 근로자 계정을 생성하는 경우 ID를 0으로 입력한다.
     * 이용 가능 대상 : 해외 관리 기관
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/requestFromJsonForWorker/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *         @OA\MediaType (
     *             mediaType="application/json",
     *             @OA\Schema (ref="#components/schemas/visa_application_data")
     *         )
     *     ),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/visa_master_info")
     *             }
     *         )
     *     ),
     *     @OA\Response (
     *         response=201,
     *         description="등록되었으나 웹에서 추가작업 필요",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/visa_master_info")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function requestFromJsonForWorker(Request $request, int $id) : JsonResponse {
        try {
            $visa_service = VisaApplicationService::getInstance();
            $worker_service = WorkerManagementService::getInstance();
            if($id != 0) {
                $worker = User::findMe($id);
                if(!$worker) throw HttpException::getInstance(404);
                $dto = VisaApplicationJsonDto::createFromRequest($request, $worker);
            }
            else {
                $adto = WorkerAccountJsonDto::createFromRequest($request, $worker_service->getManager());
                if($adto->isCreateAble()) {
                    $worker = $worker_service->joinWorkerFromArray($adto->toArray());
                    if(!$worker) throw HttpErrorsException::getInstance([__('errors.user.create_failed')], 406);
                    $dto = VisaApplicationJsonDto::createFromRequest($request, $worker);
                }
            }

            $result = $visa_service->requestVisaApplicationFromJson($dto, $worker);
            if($result['visa'] instanceof VisaApplication) {
                return new Data($result['visa']->toArray(), $result['code']);
            }
            else throw HttpErrorsException::getInstance([__('errors.visa.cannot_request')], 406);
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 스켄한 근로자의 여권정보를 JSON 문자열로 받아 반영한다.
     * 이용 가능 대상 : 근로자 본인, 해외 관리 기관 및 실무자
     * @param Request $request
     * @param VisaApplication $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/visa/updatePassportFromJson/{id}",
     *     tags={"visa"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *         @OA\MediaType (
     *             mediaType="application/json",
     *             @OA\Schema (ref="#/components/schemas/passport_data")
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
    public function updatePassportFromJson(Request $request, VisaApplication $id) : JsonResponse {
        try {
            $service = VisaApplicationService::getInstance();
            $dto = VisaPassportJsonDto::createFromRequest($request);
            $service->updatePassportFromJson($dto, $id);
            return new Message();
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }
}
