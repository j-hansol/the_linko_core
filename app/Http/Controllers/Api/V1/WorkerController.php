<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\V1\PreSaveWorkerDto;
use App\DTOs\V1\PreSaveWorkerFromExcelDto;
use App\DTOs\V1\VisaDocumentDto;
use App\DTOs\V1\WorkerAccountDto;
use App\DTOs\V1\WorkerEducationDto;
use App\DTOs\V1\WorkerEtcExperienceFileDto;
use App\DTOs\V1\WorkerExperienceDto;
use App\DTOs\V1\WorkerFamilyDto;
use App\DTOs\V1\WorkerInfoDto;
use App\DTOs\V1\WorkerPassportDto;
use App\DTOs\V1\WorkerPassportFileDto;
use App\DTOs\V1\WorkerPassportJsonDto;
use App\DTOs\V1\WorkerResumeDto;
use App\DTOs\V1\WorkerVisaDocumentsDto;
use App\DTOs\V1\WorkerVisitCountryDto;
use App\Http\Controllers\Controller;
use App\Http\JsonResponses\Common\Data;
use App\Http\JsonResponses\Common\ErrorMessage;
use App\Http\JsonResponses\Common\Message;
use App\Http\JsonResponses\V1\Base\WorkerEducationInfo;
use App\Http\JsonResponses\V1\Base\WorkerExperienceInfo;
use App\Http\JsonResponses\V1\Base\WorkerInfoResponse;
use App\Http\JsonResponses\V1\List\PreSavedWorkerInfos;
use App\Http\JsonResponses\V1\List\UserInfos;
use App\Http\JsonResponses\V1\List\VisitCountryInfos;
use App\Http\JsonResponses\V1\List\WorkerEducationInfos;
use App\Http\JsonResponses\V1\List\WorkerEtcExperienceFileInfos;
use App\Http\JsonResponses\V1\List\WorkerExperienceInfos;
use App\Http\JsonResponses\V1\List\WorkerFamilyList;
use App\Http\JsonResponses\V1\List\WorkerPassports;
use App\Http\JsonResponses\V1\List\WorkerResumeInfos;
use App\Http\JsonResponses\V1\List\WorkerVisaDocumentInfos;
use App\Http\QueryParams\CountryParam;
use App\Http\QueryParams\ListQueryParam;
use App\Http\Requests\V1\RequestJoinWorker;
use App\Http\Requests\V1\RequestPassportFile;
use App\Http\Requests\V1\RequestPreSaveFromExcel;
use App\Http\Requests\V1\RequestPreSaveWorkerInfo;
use App\Http\Requests\V1\RequestSetWorkerInfo;
use App\Http\Requests\V1\RequestUpdateWorker;
use App\Http\Requests\V1\RequestVisaDocument;
use App\Http\Requests\V1\RequestWorkerEducation;
use App\Http\Requests\V1\RequestWorkerExperience;
use App\Http\Requests\V1\RequestWorkerFamily;
use App\Http\Requests\V1\RequestWorkerPassport;
use App\Http\Requests\V1\RequestWorkerResume;
use App\Http\Requests\V1\RequestWorkerVisaDocuments;
use App\Http\Requests\V1\RequestWorkerVisitedCountry;
use App\Lib\PageCollection;
use App\Lib\PageResult;
use App\Models\PreSaveWorkerInfo;
use App\Models\User;
use App\Models\WorkerEducation;
use App\Models\WorkerEtcExperienceFile;
use App\Models\WorkerExperience;
use App\Models\WorkerFamily;
use App\Models\WorkerInfo;
use App\Models\WorkerPassport;
use App\Models\WorkerResume;
use App\Models\WorkerVisaDocument;
use App\Models\WorkerVisit;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use App\Services\V1\WorkerManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @OA\Tag(
 *     name="worker",
 *     description="근로자관련 기능"
 * )
 */
class WorkerController extends Controller {
    /**
     * 관리대상 권로자 목록을 리턴한다.
     * 이용 가능 대상 : 근로자 관리 기관 및 실무자
     * @param ListQueryParam $param
     * @param CountryParam $country_param
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/listWorker",
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
     * )
     */
    public function listWorker(ListQueryParam $param, CountryParam $country_param) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new UserInfos(new PageResult($service->listWorker($param, $country_param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 관리 근로자 계정을 생성(가입)한다.
     * 이용 가능 대상 : 근로자 기관 및 실무자
     * @param RequestJoinWorker $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/worker/joinWorker",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (
     *                  allOf={
     *                      @OA\Schema (ref="#/components/schemas/join_common"),
     *                      @OA\Schema (ref="#/components/schemas/join_password"),
     *                      @OA\Schema (ref="#/components/schemas/join_worker_profile"),
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
    public function joinWorker(RequestJoinWorker $request) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerAccountDto::createFromRequest($request);
            $service->joinWorker($dto);
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
     * 근로자 계정 정보를 리턴한다.
     * 이용 가능 대상 : 해외 관리 기관 및 실무자
     * @param User $id
     * @return JsonResponse
     * @OA\Get (
     *      path="/worker/getWorker/{id}",
     *      tags={"worker"},
     *      security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *      @OA\Parameter (ref="#/components/parameters/id"),
     *       @OA\Response (
     *           response=200,
     *           description="요청 성공",
     *           @OA\JsonContent (
     *               allOf={
     *                   @OA\Schema (ref="#/components/schemas/api_message"),
     *                   @OA\Schema(ref="#/components/schemas/user_info")
     *               }
     *           )
     *       ),
     *       @OA\Response (response=401, ref="#/components/responses/401"),
     *       @OA\Response (response=500, ref="#/components/responses/500")
     *  )
     */
    public function getWorker(User $id) : JsonResponse {
        try {
            return $id->response();
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자 계정 정보를 수정한다.
     * 이용 가능 대상 : 해외 관리 기관 및 실무자
     * @param RequestUpdateWorker $request
     * @param User $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/worker/updateWorker/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *         @OA\MediaType (
     *             mediaType="multipart/form-data",
     *             @OA\Schema (
     *                 allOf={
     *                     @OA\Schema (ref="#/components/schemas/join_common"),
     *                     @OA\Schema (ref="#/components/schemas/join_password"),
     *                     @OA\Schema (ref="#/components/schemas/join_worker_profile"),
     *                 },
     *                 required={"password"}
     *             )
     *         )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function updateWorker(RequestUpdateWorker $request, User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerAccountDto::createFromRequest($request);
            $service->updateWorker($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            Log::debug('오류' . $e->getMessage(), $e->getTrace());
            return new Message(500);
        }
    }

    /**
     * 근로자의 사진을 등록한다.
     * 이용 가능 대상 : 해외 관리 기관 및 실무자
     * @param Request $request
     * @param User $id
     * @return JsonResponse
     * @OA\Post (
     *      path="/worker/updateWorkerPhoto/{id}",
     *      tags={"worker"},
     *      security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *      @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (
     *                  allOf={@OA\Schema (ref="#/components/schemas/image")}
     *              )
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function updateWorkerPhoto(Request $request, User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $validator = Validator::make($request->allFiles(), ['image' => ['required', 'image']]);
            if($validator->fails()) throw HttpErrorsException::getInstance($validator->getMessageBag()->toArray(), 400);
            $service->updatePhoto($request->file('image'), $id);
            return new Message();
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자의 초기 암호화된 비밀번호를 출력한다.
     * 이용 가능 대상 : 해외 관리 기관 및 실무자
     * @param Request $request
     * @param User $id
     * @return JsonResponse
     * @OA\Schema (
     *     schema="initial_password",
     *     title="초기 비밀번호",
     *     @OA\Property (
     *          property="password",
     *          type="string",
     *          description="비밀번호"
     *     )
     * )
     * @OA\Get(
     *     path="/worker/getInitialPassword/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/initial_password"),
     *             }
     *         )
     *     ),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function getInitialPassword(Request $request, User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new Data(['password' => $service->getInitialPassword($id)]);
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 근로자를 관리중인 근로자 목록에서 제외한다. 지정 일련번호의 계정이 근로자가 아닌 경우 실행을 거부한다.
     * 이용 가능 대상 : 근로자 관리 기관 및 실무자
     * @param User $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/cancelManaging/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=406, ref="#/components/responses/406"),
     *      @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function cancelManaging(User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $service->cancelManaging($id);
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
     * 임시 저장된 근로자 정보 목록을 출력한다.
     * 이용 가능 대상 : 근로자 관리 기관 및 실무자
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/listPreSavedWorker",
     *     tags={"worker"},
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
     *                 @OA\Schema (ref="#/components/schemas/pre_save_worker_info_list")
     *             }
     *         )
     *     ),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listPreSavedWorker(ListQueryParam $param) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new PreSavedWorkerInfos(new PageResult($service->listPreSavedWorker($param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자 정보를 저장한다. 해당 정보로 계정생성 가능한 경우 생성할 수 있도록 한다.
     * 이용 가능 대상 : 근로자 관리 기관 및 실무자
     * @param RequestPreSaveWorkerInfo $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/worker/preSaveWorker",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (ref="#/components/schemas/presave_worker_profile"),
     *          )
     *     ),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function preSaveWorker(RequestPreSaveWorkerInfo $request) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = PreSaveWorkerDto::createFromRequest($request);
            $service->preSaveWorker($dto);
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
     * 엑셀파일을 이용하여 근로자 정보를 등록한다.
     * 이용 가능 대상 : 근로자 관리 기관 및 실무자
     * @param RequestPreSaveFromExcel $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/worker/preSaveWorkerFromExcel",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (ref="#/components/schemas/presave_worker_from_excel"),
     *          )
     *     ),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function preSaveWorkerFromExcel(RequestPreSaveFromExcel $request) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = PreSaveWorkerFromExcelDto::createFromRequest($request);
            return new Data($service->preSaveWorkerFromExcel($dto)->toArray());
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 임시저장 근로자 정보를 변경한다. 이미 가입된 근로자 저장정보의 경우 수정할 수 없다.
     * 이용 가능 대상 : 근로자 관리 기관 및 실무자
     * @param RequestPreSaveWorkerInfo $request
     * @param PreSaveWorkerInfo $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/updatePreSavedWorker/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (ref="#/components/schemas/presave_worker_profile"),
     *          )
     *     ),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function updatePreSavedWorker(RequestPreSaveWorkerInfo $request, PreSaveWorkerInfo $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = PreSaveWorkerDto::createFromRequest($request);
            $service->updatePreSavedWorker($dto, $id);
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
     * 임시저장된 근로자 정보를 삭제한다.
     * 이용 가능 대상 : 근로자 관리 기관 및 실무자
     * @param PreSaveWorkerInfo $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/deletePreSavedWorker/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function deletePreSavedWorker(PreSaveWorkerInfo $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $service->deletePreSaveWorker($id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자 채용(고용)을 위한 정보를 출력한다.
     * 이용 가능 대상 : 근로자 본인 또는 근로자 관리 기관 및 실무자
     * @param User $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/getInfo/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/model_id"),
     *                 @OA\Schema (ref="#/components/schemas/worker_info"),
     *                 @OA\Schema (ref="#/components/schemas/model_timestamps")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getInfo(User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new WorkerInfoResponse($service->getInfo($id));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자 채용(고용)을 위한 정보를 출력한다.
     *  이용 가능 대상 : 근로자 본인 또는 근로자 관리 기관 및 실무자
     * @param User $id
     * @return JsonResponse
     * @OA\Get(
     *      path="/worker/getInfoOrNull/{id}",
     *      tags={"worker"},
     *      security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *      @OA\Parameter (ref="#/components/parameters/id"),
     *      @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/model_id"),
     *                  @OA\Schema (ref="#/components/schemas/worker_info"),
     *                  @OA\Schema (ref="#/components/schemas/model_timestamps")
     *              }
     *          )
     *      ),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     *  )
     */
    public function getInfoOrNull(User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $info = $service->getInfoOrNull($id);
            return new WorkerInfoResponse($info);
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자 본인의 채옹(고용) 정보를 설정한다.
     * 이용 가능 대상 : 근로자 본인 또는 근로자 관리 기관 및 실무자
     * @param RequestSetWorkerInfo $request
     * @param User $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/worker/setInfo/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (ref="#/components/schemas/updatable_worker_info")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function setInfo(RequestSetWorkerInfo $request, User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerInfoDto::createFromRequest($request);
            $service->setInfo($dto, $id);;
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            Log::debug('오류 발생' . $e->getMessage(), $e->getTrace());
            return new Message(500);
        }
    }

    /**
     * 근로자 본인의 방문국가 목록을 리턴한다.
     * 이용 가능 대상 : 근로자 본인 또는 근로자 관리 기관 및 실무자
     * @param User $id
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get (
     *     path="/worker/listVisitedCountry/{id}",
     *     tags={"worker"},
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
     *                 @OA\Schema (ref="#/components/schemas/worker_visited_country_list")
     *             }
     *         )
     *     ),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listVisitedCountry(User $id, ListQueryParam $param) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $result = $service->listVisitedCountry($id, $param);
            return new VisitCountryInfos(new PageResult($result, $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자의지정 방문정보를 리턴한다.
     * 이용 가능 대상 : 근로자 본인 또는 근로자 관리 기관 및 실무자
     * @param WorkerVisit $id
     * @return JsonResponse
     * @OA\Get (
     *     path="/worker/getVisitedCountry/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/worker_visited_country")
     *             }
     *         )
     *     ),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getVisitedCountry(WorkerVisit $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new Data($service->getVisitedCountry($id)->toInfoArray());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자 방문국가 정보를 등록한다.
     * 이용 가능 대상 : 근로자 본인 또는 근로자 관리 기관 및 실무자
     * @param RequestWorkerVisitedCountry $request
     * @param User $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/worker/addVisitedCountry/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (allOf={@OA\Schema (ref="#/components/schemas/updatable_visited_country")})
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function addVisitedCountry(RequestWorkerVisitedCountry $request, User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerVisitCountryDto::createFromRequest($request);
            $service->addVisitedCountry($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자의 지정 방문국가정보 내용을 변경한다.
     * 이용 가능 대상 : 근로자 본인 또는 근로자 관리 기관 및 실무자
     * @param RequestWorkerVisitedCountry $request
     * @param WorkerVisit $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/worker/updateVisitedCountry/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (allOf={@OA\Schema (ref="#/components/schemas/updatable_visited_country")})
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function updateVisitedCountry(RequestWorkerVisitedCountry $request, WorkerVisit $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerVisitCountryDto::createFromRequest($request);
            $service->updateVisitedCountry($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자의 방문국가 정보를 삭제한다. 다른 자료에서 참조 중이면 삭제할 수 없다.
     * 이용 가능 대상 : 근로자 본인 또는 근로자 관리 기관 및 실무자
     * @param WorkerVisit $id
     * @return JsonResponse
     * @OA\Get (
     *     path="/worker/deleteVisitedCountry/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=406, ref="#/components/responses/406"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function deleteVisitedCountry(WorkerVisit $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $service->deleteVisitedCountry($id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자의 가족 목록을 리턴한다.
     * 이용 가능 대상 : 근로자 본인 또는 근로자 관리 기관 및 실무자
     * @param ListQueryParam $param
     * @param User $id
     * @return JsonResponse
     * @OA\Get (
     *     path="/worker/listFamily/{id}",
     *     tags={"worker"},
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
     *                 @OA\Schema (ref="#/components/schemas/worker_family_list")
     *             }
     *         )
     *     ),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listFamily(ListQueryParam $param, User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new WorkerFamilyList(new PageResult($service->listFamily($id, $param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자 본인의 지정 가족정보를 리턴한다.
     * 이용 가능 대상 : 근로자 본인 또는 근로자 관리 기관 및 실무자
     * @param WorkerFamily $id
     * @return JsonResponse
     * @OA\Get (
     *     path="/worker/getFamily/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/worker_family")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getFamily(WorkerFamily $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new Data($service->getFamily($id)->toArray());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 곤로자의 가족정보를 등록한다.
     * 이용 가능 대상 : 근로자 본인 또는 근로자 관리 기관 및 실무자
     * @param RequestWorkerFamily $request
     * @param User $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/worker/addFamily/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (ref="#/components/schemas/updatable_worker_family")
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function addFamily(RequestWorkerFamily $request, User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerFamilyDto::createFromRequest($request);
            $service->addFamily($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자의 가족정보를 변경한다.
     * 이용 가능 대상 : 근로자 본인 또는 근로자 관리 기관 및 실무자
     * @param RequestWorkerFamily $request
     * @param WorkerFamily $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/worker/updateFamily/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (allOf={@OA\Schema (ref="#/components/schemas/updatable_worker_family")})
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function updateFamily(RequestWorkerFamily $request, WorkerFamily $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerFamilyDto::createFromRequest($request);
            $service->updateFamily($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자의 지정 가족정보를 삭제한다. 다른 자료에서 참조하는 경우 삭제할 수 없다.
     * 이용 가능 대상 : 근로자 본인 또는 근로자 관리 기관 및 실무자
     * @param WorkerFamily $id
     * @return JsonResponse
     * @OA\Get (
     *     path="/worker/deleteFamily/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=406, ref="#/components/responses/406"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function deleteFamily(WorkerFamily $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $service->deleteFamily($id);
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
     * 스켄한 근로자의 여권정보를 JSON 문자열로 받아 반영한다.
     * 이용 가능 대상 : 근로자 본인, 해외 관리 기관 및 실무자
     * @param Request $request
     * @param User $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/addPassportForWorker/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *         @OA\MediaType (
     *             mediaType="application/json",
     *             @OA\Schema (ref="#/components/schemas/passport_data")
     *         )
     *     ),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (
     *                     @OA\Property (property="passport_id", type="integer", description="등록된 여권정보 일련번호")
     *                 )
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
    public function addPassportForWorker(Request $request, User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerPassportJsonDto::createFromRequest($request);
            return new Data(['passport_id' => $service->addPassport($dto, $id)?->id]);
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자의 여권정보를 받아 반영한다.
     * 이용 가능 대상 : 근로자 본인, 해외 관리 기관 및 실무자
     * @param RequestWorkerPassport $request
     * @param User $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/worker/addPassportForWorkerFromForm/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (allOf={@OA\Schema (ref="#/components/schemas/input_worker_passport")})
     *          )
     *     ),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (
     *                     @OA\Property (property="passport_id", type="integer", description="등록된 여권정보 일련번호")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function addPassportForWorkerFromForm(RequestWorkerPassport $request, User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerPassportDto::createFromRequest($request);
            return new Data(['passport_id' => $service->addPassport($dto, $id)?->id]);
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 여권정보를 받아 변경한다..
     * 이용 가능 대상 : 근로자 본인, 해외 관리 기관 및 실무자
     * @param RequestWorkerPassport $request
     * @param WorkerPassport $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/worker/updatePassportForWorkerFromForm/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (allOf={@OA\Schema (ref="#/components/schemas/input_worker_passport")})
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function updatePassportForWorkerFromForm(RequestWorkerPassport $request, WorkerPassport $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerPassportDto::createFromRequest($request);
            $service->updatePassport($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 여권정보를 삭제한다.
     * @param WorkerPassport $id
     * @return JsonResponse
     * @OA\Get (
     *     path="/worker/deletePassport/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function deletePassport(WorkerPassport $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $service->deletePassport($id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 등록된 여권 정보의 이미지 또는 파일을 등록한다.
     * 이용 가능 대상 : 근로자 본인, 근로자 관리기관 및 실무자
     * @param RequestPassportFile $request
     * @param WorkerPassport $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/setPassportFile/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *         @OA\MediaType (
     *             mediaType="multipart/form-data",
     *             @OA\Schema (ref="#/components/schemas/input_passport_file")
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
    public function setPassportFile(RequestPassportFile $request, WorkerPassport $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerPassportFileDto::createFromRequest($request);
            $service->setPassportFile($dto, $id);
            return new Message();
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자의 여권정보 목록을 출력한다.
     * @param ListQueryParam $param
     * @param User $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/listPassportForWorker/{id}",
     *     tags={"worker"},
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
     *                 @OA\Schema (ref="#/components/schemas/worker_passports")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listPassportForWorker(ListQueryParam $param, User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new WorkerPassports(new PageResult($service->listPassportForWorker($param, $id), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 여권정보를 출력한다.
     * @param WorkerPassport $id
     * @return JsonResponse
     * @OA\Get (
     *     path="/worker/getPassport/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/worker_passport")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getPassport(WorkerPassport $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new Data($service->getPassport($id)->toInfoArray());
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자 본인 및 관리기관, 관리기관 실무자에게 여권 사진을 출력한다.
     *  이용 가능 대상 : 근로자 본인, 근로자 관리기관 및 실무자
     * @param WorkerPassport $id
     * @return JsonResponse|StreamedResponse
     * @OA\Get (
     *     path="/worker/showPassportFile/{id}",
     *     tags={"worker"},
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
    public function showPassportFile(WorkerPassport $id) : JsonResponse|StreamedResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return $service->showPassportFile($id);
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자의 비자신청관련 문서 목록을 출력한다.
     * 이용 가능 대상 : 근로자 본인, 근로자 관리기관 및 실무자
     * @param User $id
     * @param ListQueryParam $param
     * @return JsonResponse
     * @throws \Exception
     * @OA\Get(
     *     path="/worker/listVisaDocument/{id}",
     *     tags={"worker"},
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
     *                 @OA\Schema (ref="#/components/schemas/worker_visa_documents")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listVisaDocument(User $id, ListQueryParam $param) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new WorkerVisaDocumentInfos(new PageResult($service->listDocument($id, $param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자의 비자발급에 필요한 문서를 등록한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestVisaDocument $request
     * @param User $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/addVisaDocumentForWorker/{id}",
     *     tags={"worker"},
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
    public function addVisaDocumentForWorker(RequestVisaDocument $request, User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
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
     * 근로자의 비자발급에 필요한 문서를 등록한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestWorkerVisaDocuments $request
     * @param User $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/addVisaDocumentsForWorker/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/worker_visa_documents_input")
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
    public function addVisaDocumentsForWorker(RequestWorkerVisaDocuments $request, User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerVisaDocumentsDto::createFromRequest($request);
            $service->addDocuments($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 등록된 문서를 변경한다.
     * 이용 가능 대상 : 비자 발급 신청자 본인 및 근로자를를 관리하는 기관 및 실무자
     * @param RequestVisaDocument $request
     * @param WorkerVisaDocument $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/updateVisaDocument/{id}",
     *     tags={"worker"},
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
    public function updateVisaDocument(RequestVisaDocument $request, WorkerVisaDocument $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
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
     * @param WorkerVisaDocument $id
     * @return mixed
     * @OA\Get(
     *     path="/worker/showVisaDocument/{id}",
     *     tags={"worker"},
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
    public function showVisaDocument(WorkerVisaDocument $id) : mixed {
        try {
            $service = WorkerManagementService::getInstance();
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
     * @param WorkerVisaDocument $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/deleteVisaDocument/{id}",
     *     tags={"worker"},
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
    public function deleteVisaDocument(WorkerVisaDocument $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $service->deleteDocument($id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자 본인의 이력서 목록을 출력한다.
     * 이용 가능 대상 : 근로자 본인
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/listResume/{id}",
     *     tags={"worker"},
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
     *                 @OA\Schema (ref="#/components/schemas/worker_resume_infos")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listResume(ListQueryParam $param) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new WorkerResumeInfos(new PageResult($service->listWorkerResume(current_user(), $param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 근로자의 이력서 목록을 출력한다.
     * 이용 가능 대상 : 근로자 관리 기관
     * @param User $id
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/listWorkerResume/{id}",
     *     tags={"worker"},
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
     *                 @OA\Schema (ref="#/components/schemas/worker_resume_infos")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listWorkerResume(User $id, ListQueryParam $param) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new WorkerResumeInfos(new PageResult($service->listWorkerResume($id, $param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자 본인의 이력서를 등록한다.
     * 이용 가능 대상 : 근로자 본인
     * @param RequestWorkerResume $request
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/addResume",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_worker_resume")
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
    public function addResume(RequestWorkerResume $request) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerResumeDto::createFromRequest($request);
            $service->addWorkerResume(current_user(), $dto);;
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 근로자의 이력서를 등록한다.
     * 이용 가능 대상 : 근로자 관리 기관
     * @param RequestWorkerResume $request
     * @param User $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/addWorkerResume/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_worker_resume")
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
    public function addWorkerResume(RequestWorkerResume $request, User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerResumeDto::createFromRequest($request);
            $service->addWorkerResume($id, $dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            Log::debug('이력서 등록 오류 발생', ['message' => $e->getMessage(), 'errors' => $e->getTrace()]);
            return new Message(500);
        }
    }

    /**
     * 지정 이력서를 변경한다.
     * 이용 가능 대상 : 근로자 본인 또는 관리 기관
     * @param RequestWorkerResume $request
     * @param WorkerResume $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/updateResume/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_worker_resume")
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
    public function updateResume(RequestWorkerResume $request, WorkerResume $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerResumeDto::createFromRequest($request);
            $service->updateWorkerResume($id, $dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자의 이력서를 삭제한다.
     * 이용 가능 대상 : 근로자 본인 또는 관리기관
     * @param WorkerResume $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/deleteResume/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function deleteResume(WorkerResume $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $service->deleteWorkerResume($id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자의 이력서를 출력한다.
     * 이용 가능 대상 : 근로자 본인 또는 관리 기관
     * @param WorkerResume $id
     * @return mixed
     * @OA\Get(
     *      path="/worker/showResume/{id}",
     *      tags={"worker"},
     *      security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *      @OA\Parameter (ref="#/components/parameters/id"),
     *      @OA\Parameter (ref="#/components/parameters/_token"),
     *      @OA\Response (
     *           response=200,
     *           description="요청 성공",
     *           @OA\MediaType (
     *               mediaType="application/*",
     *               @OA\Schema (
     *                   type="string",
     *                   format="binary"
     *               )
     *           )
     *      ),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500"),
     *  )
     */
    public function showResume(WorkerResume $id) : mixed {
        try {
            $service = WorkerManagementService::getInstance();
            return $service->showWorkerResumeFile($id);
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자 본인의 경력정보를 등록한다.
     * 이용 가능 대상 : 근로자 본인
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/listExperience",
     *     tags={"worker"},
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
     *                 @OA\Schema (ref="#/components/schemas/worker_experience_infos")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listExperience(ListQueryParam $param) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new WorkerExperienceInfos(
                new PageResult($service->listWorkerExperience(current_user(), $param), $param)
            );
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 근로자의 경력정보를 등록한다.
     * 이용 가능 대상 : 근로자 본인 또는 관리 기관
     * @param User $id
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/listWorkerExperience/{id}",
     *     tags={"worker"},
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
     *                 @OA\Schema (ref="#/components/schemas/worker_experience_infos")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listWorkerExperience(User $id, ListQueryParam $param) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new WorkerExperienceInfos(new PageResult($service->listWorkerExperience($id, $param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자 본인의 경력정보를 등록한다.
     * 이용 가능 대상 : 근로자 본인
     * @param RequestWorkerExperience $request
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/addExperience",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_worker_experience")
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
    public function addExperience(RequestWorkerExperience $request) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerExperienceDto::createFromRequest($request);
            $service->addWorkerExperience(current_user(), $dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 근로자의 경력정보를 등록한다.
     * 이용 가능 대상 : 근로자 관리 기관
     * @param RequestWorkerExperience $request
     * @param User $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/addWorkerExperience/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_worker_experience")
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
    public function addWorkerExperience(RequestWorkerExperience $request, User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerExperienceDto::createFromRequest($request);
            $service->addWorkerExperience($id, $dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 경력정보를 변경한다.
     * 이용 가능 대상 : 근로자 본인 또는 관리 기관
     * @param RequestWorkerExperience $request
     * @param WorkerExperience $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/updateExperience/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_worker_experience")
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
    public function updateExperience(RequestWorkerExperience $request, WorkerExperience $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerExperienceDto::createFromRequest($request);
            $service->updateWorkerExperience($id, $dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 경력정보를 삭제한다.
     * 이용 가능 대상 : 근로자 본인 또는 관리 기관
     * @param WorkerExperience $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/deleteExperience/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function deleteExperience(WorkerExperience $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $service->deleteWorkerExperience($id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 경력정보를 출력한다.
     * 이용 가능 대상 : 근로자 본인 또는 관리 기관
     * @param WorkerExperience $id
     * @return JsonResponse
     * @OA\Get(
     *      path="/worker/getExperience/{id}",
     *      tags={"worker"},
     *      security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *      @OA\Parameter (ref="#/components/parameters/id"),
     *      @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/worker_experience_info")
     *              }
     *          )
     *      ),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500"),
     *  )
     */
    public function getExperience(WorkerExperience $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new WorkerExperienceInfo($service->getWorkerExperience($id));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 경력증명서(증빙서류) 내용을 출력한다.
     * 이용 가능 대상 : 근로자 본인 또는 관리 기관
     * @param WorkerExperience $id
     * @return mixed
     * @OA\Get(
     *       path="/worker/showExperienceFile/{id}",
     *       tags={"worker"},
     *       security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *       @OA\Parameter (ref="#/components/parameters/id"),
     *       @OA\Parameter (ref="#/components/parameters/_token"),
     *       @OA\Response (
     *            response=200,
     *            description="요청 성공",
     *            @OA\MediaType (
     *                mediaType="application/*",
     *                @OA\Schema (
     *                    type="string",
     *                    format="binary"
     *                )
     *            )
     *       ),
     *       @OA\Response (response=401, ref="#/components/responses/401"),
     *       @OA\Response (response=403, ref="#/components/responses/403"),
     *       @OA\Response (response=404, ref="#/components/responses/404"),
     *       @OA\Response (response=500, ref="#/components/responses/500"),
     *   )
     */
    public function showExperienceFile(WorkerExperience $id) : mixed {
        try {
            $service = WorkerManagementService::getInstance();
            return $service->showWorkerExperienceFile($id);
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 근로자의 기타 증빙서류 파일 목록을 출력한다.
     * 이용 가능 대상 : 근로자 관리 기관
     * @param User $id
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/listWorkerEtcExperienceFile/{id}",
     *     tags={"worker"},
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
     *                 @OA\Schema (ref="#/components/schemas/worker_resume_infos")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listWorkerEtcExperienceFile(User $id, ListQueryParam $param) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new WorkerEtcExperienceFileInfos(new PageResult($service->listWorkerEtcExperienceFile($id, $param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자 본인의 기타 경력 정빙 서류 파일을 등록한다.
     * 이용 가능 대상 : 근로자 본인
     * @param RequestWorkerResume $request
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/addEtcExperienceFile",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_worker_resume")
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
    public function addEtcExperienceFile(RequestWorkerResume $request) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerEtcExperienceFileDto::createFromRequest($request);
            $service->addWorkerEtcExperience(current_user(), $dto);;
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 근로자의 기타 경력 증빙 서류 파일을 등록한다.
     * 이용 가능 대상 : 근로자 관리 기관\
     * RequestWorkerResume 구조가 같아 코드를 재활용함
     * @param RequestWorkerResume $request
     * @param User $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/addWorkerEtcExperienceFile/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_worker_resume")
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
    public function addWorkerEtcExperienceFile(RequestWorkerResume $request, User $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerEtcExperienceFileDto::createFromRequest($request);
            $service->addWorkerEtcExperience($id, $dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 기차 경력 증빙 서류파일을 변경한다.
     * 이용 가능 대상 : 근로자 본인 또는 관리 기관
     * RequestWorkerResume 구조와 같아 코드를 재활용함
     * @param RequestWorkerResume $request
     * @param WorkerEtcExperienceFile $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/updateEtcExperienceFile/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_worker_resume")
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
    public function updateEtcExperienceFile(RequestWorkerResume $request, WorkerEtcExperienceFile $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerEtcExperienceFileDto::createFromRequest($request);
            $service->updateWorkerEtcExperienceFile($id, $dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자의 기타 경력 증빙 서류 파일을 삭제한다.
     * 이용 가능 대상 : 근로자 본인 또는 관리기관
     * @param WorkerEtcExperienceFile $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/deleteEtcExperienceFile/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function deleteEtcExperienceFile(WorkerEtcExperienceFile $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $service->deleteWorkerEtcExperienceFile($id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자의 기타 경력 증빙 서류 파일 내용을 출력한다.
     * 이용 가능 대상 : 근로자 본인 또는 관리 기관
     * @param WorkerEtcExperienceFile $id
     * @return mixed
     * @OA\Get(
     *      path="/worker/showEtcExperienceFile/{id}",
     *      tags={"worker"},
     *      security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *      @OA\Parameter (ref="#/components/parameters/id"),
     *      @OA\Parameter (ref="#/components/parameters/_token"),
     *      @OA\Response (
     *           response=200,
     *           description="요청 성공",
     *           @OA\MediaType (
     *               mediaType="application/*",
     *               @OA\Schema (
     *                   type="string",
     *                   format="binary"
     *               )
     *           )
     *      ),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500"),
     *  )
     */
    public function showEtcExperienceFile(WorkerEtcExperienceFile $id) : mixed {
        try {
            $service = WorkerManagementService::getInstance();
            return $service->showWorkerEtcExperienceFile($id);
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 근로자의 학력정보 목록을 출력한다.
     * @param User $id
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/listWorkerEducation/{id}",
     *     tags={"worker"},
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
     *                 @OA\Schema (ref="#/components/schemas/worker_education_infos")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listWorkerEducation(User $id, ListQueryParam $param) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new WorkerEducationInfos(new PageResult($service->listWorkerEducation($id, $param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            Log::debug('오류' . $e->getMessage(), $e->getTrace());
            return new Message(500);
        }
    }

    /**
     * 지정 학력정보를 출력한다.
     * @param WorkerEducation $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/getWorkerEducation/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/worker_education_info")
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
    public function getWorkerEducation(WorkerEducation $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            return new WorkerEducationInfo($service->getWorkerEducation($id));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 근로자의 학력정보를 등록한다.
     * @param User $id
     * @param RequestWorkerEducation $request
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/addWorkerEducation/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_worker_education_info")
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
    public function addWorkerEducation(User $id, RequestWorkerEducation $request) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = @WorkerEducationDto::createFromRequest($request);
            $service->addWorkerEducation($id, $dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            Log::debug('에러' . $e->getMessage(), $e->getTrace());
            return new Message(500);
        }
    }

    /**
     * 지정 학력정보를 갱신한다.
     * @param RequestWorkerEducation $request
     * @param WorkerEducation $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/worker/updateWorkerEducation/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_worker_education_info")
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
    public function updateWorkerEducation(RequestWorkerEducation $request, WorkerEducation $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $dto = WorkerEducationDto::createFromRequest($request);
            $service->updateWorkerEducation($dto, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 학력정보를 삭제한다.
     * @param WorkerEducation $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/worker/deleteWorkerEducation/{id}",
     *     tags={"worker"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     * /
     */
    public function deleteWorkerEducation(WorkerEducation $id) : JsonResponse {
        try {
            $service = WorkerManagementService::getInstance();
            $service->deleteWorkerEducation($id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    public function showWorkerEducationFile(WorkerEducation $id) : mixed {
        try {
            $service = WorkerManagementService::getInstance();
            return $service->showWorkerEtcEducationFile($id);
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }
}
