<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\V1\AssignCompanyDto;
use App\DTOs\V1\ContractDto;
use App\DTOs\V1\ContractFileDto;
use App\DTOs\V1\EntryScheduleDto;
use App\DTOs\V1\SubContractDto;
use App\DTOs\V1\UnAssignedCompanyDto;
use App\DTOs\V1\UpdatePlannedWorkerCountDto;
use App\DTOs\V1\WorkerEntryScheduleDto;
use App\DTOs\V1\WorkingCompaniesDto;
use App\Http\Controllers\Controller;
use App\Http\JsonResponses\Common\ErrorMessage;
use App\Http\JsonResponses\Common\Message;
use App\Http\JsonResponses\V1\List\AssignedContractWorkers;
use App\Http\JsonResponses\V1\List\ContractManagers;
use App\Http\JsonResponses\V1\List\Contracts;
use App\Http\JsonResponses\V1\List\EntryInfos;
use App\Http\JsonResponses\V1\List\UserInfos;
use App\Http\JsonResponses\V1\List\WorkingCompanies;
use App\Http\QueryParams\ListQueryParam;
use App\Http\Requests\V1\RequestAddAssignedWorkers;
use App\Http\Requests\V1\RequestAddContract;
use App\Http\Requests\V1\RequestAddManagers;
use App\Http\Requests\V1\RequestAddWorkingCompanies;
use App\Http\Requests\V1\RequestAssignCompany;
use App\Http\Requests\V1\RequestContractFile;
use App\Http\Requests\V1\RequestDeleteAssignedWorkers;
use App\Http\Requests\V1\RequestDeleteWorkingCompanies;
use App\Http\Requests\V1\RequestEntrySchedule;
use App\Http\Requests\V1\RequestSetSubContract;
use App\Http\Requests\V1\RequestUnAssign;
use App\Http\Requests\V1\RequestUpdatePlannedWorkerCount;
use App\Http\Requests\V1\RequestUpdateWorkerStatus;
use App\Http\Requests\V1\RequestWorkerEntrySchedule;
use App\Lib\AdditionalFilter;
use App\Lib\AssignedWorkerStatus;
use App\Lib\MemberType;
use App\Lib\PageResult;
use App\Models\Contract;
use App\Models\ContractFile;
use App\Models\EntrySchedule;
use App\Models\EvalInfo;
use App\Models\User;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use App\Services\V1\ContractService;
use App\Services\V1\UserService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="contract",
 *     description="꼐약관련 기능"
 * )
 * @OA\Parameter (
 *      name="contract_id",
 *      in="path",
 *      required=true,
 *      description="계약 정보 일련번호",
 *      @OA\Schema (type="integer")
 *  )
 */
class ContractController extends Controller {
    /**
     * 발주자가 작성 중인 미공개 계약서 목록을 출력한다.
     * 이용 대상 : 발주자
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/contract/listUndisclosedContract",
     *     tags={"contract"},
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
     *                 @OA\Schema (ref="#/components/schemas/contract_infos")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listUndisclosedContract(ListQueryParam $param) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            return new Contracts(new PageResult($service->listUndisclosedContract($param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 발주처에서 발주한 게약정보 목록을 출력한다.
     * 이용 대상 : 발주자, 발주 계약관리 기관 빛 실무자
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/contract/listOrderedContract",
     *     tags={"contract"},
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
     *                 @OA\Schema (ref="#/components/schemas/contract_infos")
     *             }
     *         )
     *     ),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listOrderedContract(ListQueryParam $param) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            return new Contracts(new PageResult($service->listOrderedContract($param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 근로자 공급자(계약 대상자) 목록을 출력한다.
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *     path="/contract/listWorkerProvider",
     *     tags={"contract"},
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
    public function listWorkerProvider(ListQueryParam $param) : JsonResponse {
        try {
            $service = UserService::getInstance();
            return new UserInfos(new PageResult($service->listByType(
                MemberType::TYPE_FOREIGN_PROVIDER, $param,
                new AdditionalFilter('active', '=', '1')
            ), $param));
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 발주처에서 신규 계약정보를 등록한다.
     * 이용 대상 : 발주자
     * @param RequestAddContract $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/contract/add",
     *     tags={"contract"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/add_contract")
     *          )
     *     ),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function add(RequestAddContract $request) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $service->addContract(ContractDto::createFromRequest($request));
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 중계 계약의 경우 하위 계약을 등록한다.
     * @param RequestSetSubContract $request
     * @param Contract $id
     * @return JsonResponse
     * @OA\Post (
     *      path="/contract/setSubContract/{id}",
     *      tags={"contract"},
     *      security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *      @OA\Parameter ( ref="#/components/parameters/id"),
     *      @OA\RequestBody(
     *           @OA\MediaType(
     *               mediaType="multipart/form-data",
     *               @OA\Schema(ref="#/components/schemas/add_sub_contract")
     *           )
     *      ),
     *       @OA\Response (response=200, ref="#/components/responses/200"),
     *       @OA\Response (response=400, ref="#/components/responses/400"),
     *       @OA\Response (response=401, ref="#/components/responses/401"),
     *       @OA\Response (response=403, ref="#/components/responses/403"),
     *       @OA\Response (response=500, ref="#/components/responses/500")
     *  )
     */
    public function setSubContract(RequestSetSubContract $request, Contract $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $service->setSubContract(SubContractDto::createFromRequest($request), $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 계약정보를 출력한다.
     * @param Contract $id
     * @return JsonResponse
     * @OA\Get (
     *       path="/contract/get/{id}",
     *       tags={"contract"},
     *       security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *       @OA\Parameter ( ref="#/components/parameters/id"),
     *       @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/contract_data"),
     *              }
     *          )
     *      ),
     *        @OA\Response (response=400, ref="#/components/responses/400"),
     *        @OA\Response (response=401, ref="#/components/responses/401"),
     *        @OA\Response (response=403, ref="#/components/responses/403"),
     *        @OA\Response (response=500, ref="#/components/responses/500")
     *   )
     */
    public function get(Contract $id) : JsonResponse {
        return $id->response();
    }

    /**
     * 발주처에서 기존 계약정보를 수정한다.
     * 이용 대상 : 발주자
     * @param RequestAddContract $request
     * @param Contract $id
     * @return JsonResponse
     * @OA\Post (
     *      path="/contract/update/{id}",
     *      tags={"contract"},
     *      security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *      @OA\Parameter ( ref="#/components/parameters/id"),
     *      @OA\RequestBody(
     *           @OA\MediaType(
     *               mediaType="multipart/form-data",
     *               @OA\Schema(ref="#/components/schemas/add_contract")
     *           )
     *      ),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function update(RequestAddContract $request, Contract $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $service->updateContract($id, ContractDto::createFromRequest($request));
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 발주처에서 기존 계약정보를 삭제한다.
     * 이용 대상 : 발주자
     * @param Contract $id
     * @return JsonResponse
     * @OA\Get (
     *      path="/contract/delete/{id}",
     *      tags={"contract"},
     *      security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *      @OA\Parameter ( ref="#/components/parameters/id"),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function delete(Contract $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $service->deleteContract($id);
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
     * 지정 계역정보에 등록된 관리기관 목록을 출력한다.
     * 이용 대상 : 발주자 또는 수주자
     * @param Contract $id
     * @return JsonResponse
     * @OA\Get (
     *       path="/contract/listManager/{id}",
     *       tags={"contract"},
     *       security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *       @OA\Parameter ( ref="#/components/parameters/id"),
     *       @OA\Response (response=400, ref="#/components/responses/400"),
     *       @OA\Response (response=401, ref="#/components/responses/401"),
     *       @OA\Response (response=403, ref="#/components/responses/403"),
     *       @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listManager(Contract $id) : JsonResponse {
        try {
            $contract_service = ContractService::getInstance();
            $user_service = UserService::getInstance();
            $ids = $contract_service->getManagerUserId($id);
            return new ContractManagers($user_service->findUsers($ids));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 계약정보에 관리기관을 추가한다.
     * 이용 대상 : 발주자, 수주자
     * @param Contract $contract_id
     * @param User $id
     * @return JsonResponse
     * @OA\Get (
     *     path="/contract/addManager/{contract_id}/{id}",
     *     tags={"contract"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/contract_id"),
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function addManager(Contract $contract_id, User $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $service->addContractManager($contract_id, $id);
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
     * 지정 계약의 관리기관을 설정한다. 기존 설정 무시
     * 이용 가능 대상 : 발주자, 수주자
     * @param RequestAddManagers $request
     * @param Contract $contract_id
     * @return JsonResponse
     * @OA\Post(
     *     path="/contract/setManagers/{id}",
     *     tags={"contract"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/manager_user_ids")
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
    public function setManagers(RequestAddManagers $request, Contract $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $manager_user_ids = explode(',', $request->input('manager_user_ids'));
            $service->setContractmanagers($id, $manager_user_ids);
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
     * 지정 계약정보에서 관리기관을 삭제한다.
     * 이용 대상 : 발주자
     * @OA\Get (
     *     path="/contract/deleteManager/{contract_id}/{id}",
     *     tags={"contract"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/contract_id"),
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     * @param Contract $contract_id
     * @param User $id
     * @return JsonResponse
     */
    public function deleteManager(Contract $contract_id, User $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $service->deleteContractManager($contract_id, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

     /**
     * 계약관련 파일을 등록한다.
     * 이용 대상 : 발주자, 수주자, 계약 관리 기관
     * @param RequestContractFile $request
     * @param Contract $id
     * @return JsonResponse
     * @OA\Post (
     *     path="/contract/addFile/{id}",
     *     tags={"contract"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter ( ref="#/components/parameters/id"),
     *     @OA\RequestBody(
     *        @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(ref="#/components/schemas/add_contract_file")
     *        )
     *     ),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function addFile(RequestContractFile $request, Contract $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $service->addContractFile($id, ContractFileDto::createFromRequest($request));
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 계약관련 파일을 변경한다.
     * 이용 대상 : 등록자
     * @param RequestContractFile $request
     * @param ContractFile $id
     * @return JsonResponse
     * @OA\Post (
     *        path="/contract/updateFile/{id}",
     *        tags={"contract"},
     *        security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *        @OA\Parameter ( ref="#/components/parameters/id"),
     *        @OA\RequestBody(
     *           @OA\MediaType(
     *               mediaType="multipart/form-data",
     *               @OA\Schema(ref="#/components/schemas/add_contract_file")
     *           )
     *        ),
     *        @OA\Response (response=400, ref="#/components/responses/400"),
     *        @OA\Response (response=401, ref="#/components/responses/401"),
     *        @OA\Response (response=403, ref="#/components/responses/403"),
     *        @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function updateFile(RequestContractFile $request, ContractFile $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $service->updateContractFile($id, ContractFileDto::createFromRequest($request));
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 계약관련 파일을 삭제한다.
     * 이용 대상 : 등록자
     * @param ContractFile $id
     * @return JsonResponse
     * @OA\Get (
     *       path="/contract/deleteFile/{id}",
     *       tags={"contract"},
     *       security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *       @OA\Parameter ( ref="#/components/parameters/id"),
     *       @OA\Response (response=400, ref="#/components/responses/400"),
     *       @OA\Response (response=401, ref="#/components/responses/401"),
     *       @OA\Response (response=403, ref="#/components/responses/403"),
     *       @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function deleteFile(ContractFile $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $service->deleteContractFile($id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 계약 근로자 목록을 출력한다.
     * @param Contract $id
     * @param ListQueryParam $param
     * @return JsonResponse
     * @OA\Get(
     *      path="/contract/listWorker/{id}",
     *      tags={"contract"},
     *      security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *      @OA\Parameter ( ref="#/components/parameters/id"),
     *      @OA\Parameter ( ref="#/components/parameters/filter"),
     *      @OA\Parameter ( ref="#/components/parameters/op"),
     *      @OA\Parameter ( ref="#/components/parameters/keyword"),
     *      @OA\Parameter ( ref="#/components/parameters/page"),
     *      @OA\Parameter ( ref="#/components/parameters/page_per_items"),
     *      @OA\Parameter ( ref="#/components/parameters/order"),
     *      @OA\Parameter ( ref="#/components/parameters/dir"),
     *      @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/page_info"),
     *                  @OA\Schema (ref="#/components/schemas/assigned_workers")
     *              }
     *          )
     *      ),
     *       @OA\Response (response=401, ref="#/components/responses/401"),
     *       @OA\Response (response=404, ref="#/components/responses/404"),
     *       @OA\Response (response=500, ref="#/components/responses/500")
     *  )
     */
    public function listWorker( Contract $id, ListQueryParam $param) : JsonResponse {
        try {
            $service = ContractService::getInstance();;
            return new AssignedContractWorkers(new PageResult($service->listAssignedWorker($id, $param), $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 계약에 다수의 근로자를 배정한다.
     * 이용 대상 : 수주 계약 관리기관
     * @param RequestAddAssignedWorkers $request
     * @param Contract $id
     * @return JsonResponse
     * @OA\Post(
     *      path="/contract/assignWorkers/{id}",
     *      tags={"contract"},
     *      security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *      @OA\Parameter (ref="#/components/parameters/id"),
     *      @OA\RequestBody (
     *           @OA\MediaType (
     *               mediaType="multipart/form-data",
     *               @OA\Schema(ref="#/components/schemas/worker_ids")
     *           )
     *      ),
     *       @OA\Response (response=200, ref="#/components/responses/200"),
     *       @OA\Response (response=400, ref="#/components/responses/400"),
     *       @OA\Response (response=401, ref="#/components/responses/401"),
     *       @OA\Response (response=403, ref="#/components/responses/403"),
     *       @OA\Response (response=404, ref="#/components/responses/404"),
     *       @OA\Response (response=406, ref="#/components/responses/406"),
     *       @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function assignWorkers(RequestAddAssignedWorkers $request, Contract $id) : JsonResponse {
        try {
            $contract_service = ContractService::getInstance();
            $user_service = UserService::getInstance();
            $worker_ids = explode(',', $request->input('worker_ids'));
            $ids = $user_service->findUsers($worker_ids)->pluck('id')->toArray();
            $contract_service->assignWorkers($id, $ids);
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
     * 지정 계약에 배정된 다수의 근로자를 삭제한다.
     * 이용 대상 : 수주 계약 관리 기관 및 실무자
     * @param RequestDeleteAssignedWorkers $request
     * @param Contract $id
     * @return JsonResponse
     * @OA\Post(
     *      path="/contract/deleteWorker",
     *      tags={"contract"},
     *      security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *      @OA\Parameter (ref="#/components/parameters/id"),
     *      @OA\RequestBody (
     *           @OA\MediaType (
     *               mediaType="multipart/form-data",
     *               @OA\Schema(ref="#/components/schemas/worker_ids")
     *           )
     *      ),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=406, ref="#/components/responses/406"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function deleteWorker(RequestDeleteAssignedWorkers $request, Contract $id) : JsonResponse {
        try {
            $contract_service = ContractService::getInstance();
            $worker_ids = explode(',', $request->input('worker_ids'));
            $contract_service->deleteAssignedWorkers($id, $worker_ids);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 계약에 배정된 근로자 진행 상태를 변경한다.
     * @param RequestUpdateWorkerStatus $request
     * @param Contract $id
     * @return JsonResponse
     * @OA\Post(
     *      path="/contract/updateWorkerStatus/{id}",
     *      tags={"contract"},
     *      security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *      @OA\Parameter (ref="#/components/parameters/id"),
     *      @OA\RequestBody (
     *           @OA\MediaType (
     *               mediaType="multipart/form-data",
     *               @OA\Schema(
     *                  allOf={
     *                      @OA\Schema(ref="#/components/schemas/worker_ids"),
     *                      @OA\Schema(ref="#/components/schemas/AssignedWorkerStatus")
     *                  }
     *               )
     *           )
     *      ),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=406, ref="#/components/responses/406"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function updateWorkerStatus(RequestUpdateWorkerStatus $request, Contract $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $worker_ids = explode(',', $request->input('worker_ids'));
            $worker_status = $request->enum('status', AssignedWorkerStatus::class);
            $service->updateAssignedWorkerStatus($id, $worker_ids, $worker_status);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 계약에 등록된 수요 기업 목록을 출력한다.
     * @param ListQueryParam $param
     * @param Contract $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/contract/listWorkingCompany/{id}",
     *     tags={"contract"},
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
     *                 @OA\Schema (ref="#/components/schemas/working_companies")
     *             }
     *         )
     *     ),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listWorkingCompany(ListQueryParam $param, Contract $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $result = $service->listWorkingCompany($id, $param);
            return new WorkingCompanies(new PageResult($result, $param));
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 계약 수요 기업을 등록한다.
     * @param RequestAddWorkingCompanies $request
     * @param Contract $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/contract/addWorkingCompanies/{id}",
     *     tags={"contract"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_working_companies"),
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
    public function addWorkingCompanies(RequestAddWorkingCompanies $request, Contract $id) : JsonResponse {
        try {
            $service = new ContractService();
            $dto = WorkingCompaniesDto::createFromRequest($request);
            $service->addWorkingCompany($id, $dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 계약 수요 기업의 근로자 채용 계획 인원 수를 변경한다.
     * @param RequestUpdatePlannedWorkerCount $request
     * @param Contract $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/contract/updatePlannedWorkerCount/{id}",
     *     tags={"contract"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_update_working_company"),
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
    public function updatePlannedWorkerCount(RequestUpdatePlannedWorkerCount $request, Contract $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $dto = UpdatePlannedWorkerCountDto::createFromRequest($request);
            $service->updatePlannedWorkerCount($id, $dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 계약 수요 기업을 삭제한다.
     *
     * @param RequestDeleteWorkingCompanies $request
     * @param Contract $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/contract/deleteWorkingCompanies/{id}",
     *     tags={"contract"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/ids"),
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
    public function deleteWorkingCompanies(RequestDeleteWorkingCompanies $request, Contract $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $ids = explode(',', $request->input('ids'));
            $service->deleteWorkingCompanies($id, $ids);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 계약에 등록된 근로자를 수요 기업에 배정한다.
     * 이용 가능 대상 : 발주처 관리기관 및 실무자
     * @param RequestAssignCompany $request
     * @param Contract $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/contract/assignCompany/{id}",
     *     tags={"contract"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_assign_worker"),
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=468, ref="#/components/responses/468"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function assignCompany(RequestAssignCompany $request, Contract $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $dto = AssignCompanyDto::createFromRequest($request);
            $service->assignCompany($id, $dto);
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
     * 지정 계약에 등록된 근로자를 수요 기업 배정에서 해제한다.
     * 이용 가능 대상 : 발주처 관리기관 및 실무자
     * @param RequestUnAssign $request
     * @param Contract $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/contract/unAssignCompany/{id}",
     *     tags={"contract"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/input_unassign_worker"),
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
    public function unAssignCompany(RequestUnAssign $request, Contract $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $dto = UnAssignedCompanyDto::createFromRequest($request);
            $service->unAssignCompany($id, $dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 계약의 배정 근로자 입국 일정 목록을 출력한다.
     * 이용 가능 대상 : 계약 구성원
     * @param ListQueryParam $param
     * @param Contract $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/contract/listEntrySchedule/{id}",
     *     tags={"contract"},
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
     *                 @OA\Schema (ref="#/components/schemas/entry_infos")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function listEntrySchedule(ListQueryParam $param, Contract $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            return new EntryInfos(new PageResult($service->listEntrySchedule($id, $param), $param));
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 계약의 근로자 입국 일정을 등록한다.
     * 이용 가능 대상 : 발주처 관리기관 및 실무자
     * @param RequestEntrySchedule $request
     * @param Contract $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/contract/addEntrySchedule/{id}",
     *     tags={"contract"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *         @OA\MediaType (
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/input_entry_ifo"),
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
    public function addEntrySchedule(RequestEntrySchedule $request, Contract $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $dto = EntryScheduleDto::createFromRequest($request);
            $service->addEntrySchedule($id, $dto);
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
     * 지정 입국 일전 내용을 변경한다.
     * 이용 가능 대상 : 발주처 관리기관 및 실무자
     * @param RequestEntrySchedule $request
     * @param EntrySchedule $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/contract/updateEntrySchedule/{id}",
     *     tags={"contract"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *         @OA\MediaType (
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/input_entry_ifo"),
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
    public function updateEntrySchedule(RequestEntrySchedule $request, EntrySchedule $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $dto = EntryScheduleDto::createFromRequest($request);
            $service->updateEntrySchedule($id, $dto);
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
     * 지정 입국일정 정보를 삭제한다.
     * @param EntrySchedule $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/contract/deleteEntrySchedule/{id}",
     *     tags={"contract"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function deleteEntrySchedule(EntrySchedule $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $service->deleteEntrySchedule($id);
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
     * 지정 계약의 배정 근로자 입국일정을 확정한다.
     * 이용 가능 대상 : 수주처 관리기관 및 실무자
     * @param RequestWorkerEntrySchedule $request
     * @param Contract $id
     * @return JsonResponse
     * @OA\Post(
     *     path="/contract/setWorkerEntrySchedule/{id}",
     *     tags={"contract"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\RequestBody (
     *         @OA\MediaType (
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/input_worker_entry_schedule"),
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
    public function setWorkerEntrySchedule(RequestWorkerEntrySchedule $request, Contract $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $dto = WorkerEntryScheduleDto::createFromRequest($request);
            $service->setWorkerEntrySchedule($id, $dto);
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
     * 지정 계약에서 근로자를 평가하기 위한 설문 정보를 지정한다.
     * @param Contract $contract_id
     * @param EvalInfo $id
     * @return JsonResponse
     * @OA\Get(
     *     path="/contract/setWorkerEvaluationPlan/{contract_id}/{id}",
     *     tags={"contract"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\Parameter (ref="#/components/parameters/contract_id"),
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=403, ref="#/components/responses/403"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function setWorkerEvaluationPlan(Contract $contract_id, EvalInfo $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $service->setWorkerEvaluationPlan($contract_id, $id);
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
     * 지정 계약에서 수요 기업을 평가하기 위한 설문 정무를 지정한다.
     * @param Contract $contract_id
     * @param EvalInfo $id
     * @return void
     * @OA\Get(
     *      path="/contract/setCompanyEvaluationPlan/{contract_id}/{id}",
     *      tags={"contract"},
     *      security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *      @OA\Parameter (ref="#/components/parameters/contract_id"),
     *      @OA\Parameter (ref="#/components/parameters/id"),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=403, ref="#/components/responses/403"),
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     *  )
     */
    public function setCompanyEvaluationPlan(Contract $contract_id, EvalInfo $id) : JsonResponse {
        try {
            $service = ContractService::getInstance();
            $service->setCompanyEvaluationPlan($contract_id, $id);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }
}
