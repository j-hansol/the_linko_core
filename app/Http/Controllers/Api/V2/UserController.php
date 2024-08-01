<?php

namespace App\Http\Controllers\Api\V2;

use App\DTOs\V2\AuthInfoDto;
use App\DTOs\V2\AutoLoginDto;
use App\DTOs\V2\ChangePasswordDto;
use App\DTOs\V2\DeviceInfoDto;
use App\DTOs\V2\EditableUserCommonDto;
use App\DTOs\V2\FacebookLoginDto;
use App\DTOs\V2\OrganizationDto;
use App\DTOs\V2\PasswordLoginDto;
use App\DTOs\V2\PersonProfileDto;
use App\DTOs\V2\RequestCertificationTokenDto;
use App\DTOs\V2\ResetPasswordDto;
use App\DTOs\V2\UserCommonDto;
use App\Http\Controllers\Controller;
use App\Http\JsonResponses\Common\ErrorMessage;
use App\Http\JsonResponses\Common\Message;
use App\Http\JsonResponses\V2\Base\TokenResponse;
use App\Http\JsonResponses\V2\Base\UserInfoResponse;
use App\Http\Requests\V2\RequestCertificationToken;
use App\Http\Requests\V2\RequestChangePassword;
use App\Http\Requests\V2\RequestFacebookLogin;
use App\Http\Requests\V2\RequestJoinOrganization;
use App\Http\Requests\V2\RequestJoinPerson;
use App\Http\Requests\V2\RequestLogin;
use App\Http\Requests\V2\RequestLoginAuto;
use App\Http\Requests\V2\RequestResetPassword;
use App\Http\Requests\V2\RequestUpdateDevice;
use App\Http\Requests\V2\RequestUpdateOrganizationProfile;
use App\Http\Requests\V2\RequestUpdatePersonProfile;
use App\Lib\CertificationTokenFunction;
use App\Models\Country;
use App\Models\User;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use App\Services\V2\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="user",
 *     description="회원관련 기능"
 * )
 */
class UserController extends Controller {
    /**
     * 해당 국가의 해외 파트너 정보를 리턴한다.
     * @param Country $id
     * @return JsonResponse
     *
     * @OA\Get (
     *     path="/user/getManagerByCountry/{id}",
     *     tags={"user"},
     *     @OA\Parameter (ref="#/components/parameters/id"),
     *     @OA\Response (
     *         response=200,
     *         description="요청 성공",
     *         @OA\JsonContent (
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/api_message"),
     *                 @OA\Schema (ref="#/components/schemas/user_info")
     *             }
     *         )
     *     ),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getManagerByCountry(Country $id) : JsonResponse {
        try {
            $service = UserService::getInstance();
            return $service->getManagerByCountry($id)?->response();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        }
        catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 개인회원 가입을 처리한다.
     * @param RequestJoinPerson $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/user/joinPerson",
     *     tags={"user"},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (
     *                  allOf={
     *                      @OA\Schema (ref="#/components/schemas/join_common"),
     *                      @OA\Schema (ref="#/components/schemas/join_login_method"),
     *                      @OA\Schema (ref="#/components/schemas/join_password"),
     *                      @OA\Schema (ref="#/components/schemas/person_type"),
     *                      @OA\Schema (ref="#/components/schemas/join_worker_profile"),
     *                      @OA\Schema (ref="#/components/schemas/management_org_id"),
     *                      @OA\Schema (ref="#/components/schemas/device")
     *                  }
     *              )
     *          )
     *     ),
     *     @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/login")
     *              }
     *          )
     *     ),
     *     @OA\Response (
     *          response=201,
     *          description="요청 성공 추가 승인 필요",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/login")
     *              }
     *          )
     *     ),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function joinPerson(RequestJoinPerson $request) : JsonResponse {
        try {
            $service = UserService::getInstance();
            $common = UserCommonDto::createFromRequest($request);
            $person = PersonProfileDto::createFromRequest($request);
            $auth = AuthInfoDto::createFromRequest($request);
            $device = DeviceInfoDto::createFromRequest($request);
            return new TokenResponse($service->joinPerson($common, $person, $auth, $device));
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 단체회원 가입을 처리한다.
     * @param RequestJoinOrganization $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/user/joinOrganization",
     *     tags={"user"},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (
     *                  allOf={
     *                      @OA\Schema (ref="#/components/schemas/join_common"),
     *                      @OA\Schema (ref="#/components/schemas/join_login_method"),
     *                      @OA\Schema (ref="#/components/schemas/join_password"),
     *                      @OA\Schema (ref="#/components/schemas/organization_type"),
     *                      @OA\Schema (ref="#/components/schemas/join_organization_profile"),
     *                      @OA\Schema (ref="#/components/schemas/device")
     *                  }
     *              )
     *          )
     *     ),
     *     @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/login_token")
     *              }
     *          )
     *     ),
     *     @OA\Response (
     *          response=201,
     *          description="요청 성공 (추가작업 필요)",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/login_token")
     *              }
     *          )
     *     ),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function joinOrganization(RequestJoinOrganization $request) : JsonResponse {
        try {
            $service = UserService::getInstance();
            $common = UserCommonDto::createFromRequest($request, true);
            $organization = OrganizationDto::createFromRequest($request);
            $auth = AuthInfoDto::createFromRequest($request);
            $device = DeviceInfoDto::createFromRequest($request);
            return new TokenResponse($service->joinOrganization($common, $organization, $auth, $device));
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 회원코드 및 비밀번호를 이용하여 로그인한다.
     * @param RequestLogin $request
     * @return JsonResponse
     *
     * @OA\Post (
     *     path="/user/login",
     *     tags={"user"},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (
     *                  allOf={
     *                      @OA\Schema(ref="#/components/schemas/login"),
     *                      @OA\Schema(ref="#/components/schemas/device")
     *                  }
     *              )
     *          )
     *     ),
     *     @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/login_token")
     *              }
     *          )
     *     ),
     *     @OA\Response (
     *          response=205,
     *          description="단말기 정보 상의함",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/login_token")
     *              }
     *          )
     *     ),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=465, ref="#/components/responses/465"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function login(RequestLogin $request) : JsonResponse {
        try {
            $service = UserService::getInstance();
            $dto = PasswordLoginDto::createFromRequest($request);
            return new TokenResponse($service->login($dto));
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 페이스북을 통해 로그인한다.
     * @param RequestFacebookLogin $request
     * @return JsonResponse
     *
     * @OA\Post (
     *     path="/user/loginByFacebook",
     *     tags={"user"},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (
     *                  allOf={
     *                      @OA\Schema(ref="#/components/schemas/facebook_login"),
     *                      @OA\Schema(ref="#/components/schemas/device")
     *                  }
     *              )
     *          )
     *     ),
     *     @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/login_token")
     *              }
     *          )
     *     ),
     *     @OA\Response (
     *          response=205,
     *          description="단말기 정보 상의함",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/login_token")
     *              }
     *          )
     *     ),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=406, ref="#/components/responses/406"),
     *      @OA\Response (response=465, ref="#/components/responses/465"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function loginByFacebook(RequestFacebookLogin $request) : JsonResponse {
        try {
            $service = UserService::getInstance();
            $dto = FacebookLoginDto::createFromRequest($request);
            return new TokenResponse($service->loginByFacebook($dto));
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 자동 로그인을 처리한다.
     * @param RequestLoginAuto $request
     * @return JsonResponse
     *
     * @OA\Post (
     *     path="/user/loginAuto",
     *     tags={"user"},
     *     security={{"BearerAuth":{}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (ref="#/components/schemas/device")
     *          )
     *     ),
     *     @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/login_token")
     *              }
     *          )
     *     ),
     *     @OA\Response (
     *          response=205,
     *          description="단말기 정보 상의함",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/login_token")
     *              }
     *          )
     *     ),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=465, ref="#/components/responses/465"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function loginAuto(RequestLoginAuto $request) : JsonResponse {
        try {
            $service = UserService::getInstance();
            $dto = AutoLoginDto::createFromRequest($request);
            return new TokenResponse($service->loginAuto($dto));
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 로그아웃을 처리한다.
     * @return JsonResponse
     *
     * @OA\Get (
     *     path="/user/logout",
     *     tags={"user"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=461, ref="#/components/responses/461"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function logout() : JsonResponse {
        try {
            $service = UserService::getInstance();
            $service->logout();
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
     * 계정 전환 또는 로그인 회원 자신의 정보를 리턴한다.
     * @return JsonResponse
     * @OA\Get (
     *     path="/user/getMyInfo",
     *     tags={"user"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *      @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema(ref="#/components/schemas/user_info")
     *              }
     *          )
     *      ),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=461, ref="#/components/responses/461"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getMyInfo() : JsonResponse {
        try {
            $service = UserService::getInstance();
            return new UserInfoResponse($service->getMyInfo());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 단말기 정보를 변경한다. 로그인 과정에서 응답코드가 205인 경우 필요한 기능이다.
     * @param RequestUpdateDevice $request
     * @return JsonResponse
     *
     * @OA\Post (
     *     path="/user/updateDevice",
     *     tags={"user"},
     *     security={{"BearerAuth":{}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (
     *                  allOf={@OA\Schema (ref="#/components/schemas/update_device")}
     *              )
     *          )
     *     ),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=406, ref="#/components/responses/406"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function updateDevice(RequestUpdateDevice $request) : JsonResponse {
        try {
            $service = UserService::getInstance();
            $dto = DeviceInfoDto::createFromRequest($request);
            $service->updateDevice($dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비밀번호를 변경한다.
     * @param RequestChangePassword $request
     * @return JsonResponse
     *
     * @OA\Post (
     *     path="/user/changePassword",
     *     tags={"user"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (ref="#/components/schemas/change_password")
     *          )
     *     ),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=406, ref="#/components/responses/406"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function changePassword(RequestChangePassword $request) : JsonResponse {
        try {
            $service = UserService::getInstance();
            $dto = ChangePasswordDto::createFromRequest($request);
            $service->changePassword($dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * FCM 토큰 발급 지연으로 로그인 시 등록하지 못한 경우 FCM 토큰을 등록한다.
     * @param Request $request
     * @return JsonResponse
     *
     * @OA\Post (
     *     path="/user/updateFCMToken",
     *     tags={"user"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (@OA\Property (property="fcm",type="string",description="FCM 토큰"))
     *          )
     *     ),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function updateFCMToken(Request $request) : JsonResponse {
        try {
            $service = UserService::getInstance();
            $fcm = $request->input('fcm');
            $service->updateFCMToken($fcm);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 회원코드의 회원 일련번호를 리턴한다.
     * @param string $id_alias
     * @return JsonResponse
     * @OA\Get (
     *     path="/user/getIdByIdAlias/{id_alias}",
     *     tags={"user"},
     *     @OA\Parameter ( ref="#/components/parameters/id_alias"),
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
     *      @OA\Response (response=404, ref="#/components/responses/404"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function getIdByIdAlias(string $id_alias) : JsonResponse {
        try {
            $service = UserService::getInstance();
            $user = $service->getIdByIdAlias($id_alias);
            return $user ? $user->responseId() : new Message(404);
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 단체회원 자신의 프로필 정보를 수정한다.
     * @param RequestUpdateOrganizationProfile $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/user/updateOrganizationProfile",
     *     tags={"user"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (
     *                  allOf={
     *                      @OA\Schema (ref="#/components/schemas/join_common"),
     *                      @OA\Schema (ref="#/components/schemas/join_organization_profile")
     *                  }
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
    public function updateOrganizationProfile(RequestUpdateOrganizationProfile $request) : JsonResponse {
        try {
            $service = UserService::getInstance();
            $common = EditableUserCommonDto::createFromRequest($request, true);
            $organization = OrganizationDto::createFromRequest($request);
            $service->updateOrganizationProfile($common, $organization);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 개인회원 자신의 프로필 정보를 수정한다.
     * @param RequestUpdatePersonProfile $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/user/updatePersonProfile",
     *     tags={"user"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (
     *                  allOf={
     *                      @OA\Schema (ref="#/components/schemas/join_common"),
     *                      @OA\Schema (ref="#/components/schemas/join_worker_profile"),
     *                      @OA\Schema (ref="#/components/schemas/management_org_id")
     *                  }
     *              )
     *          )
     *     ),
     *      @OA\Response (response=200, ref="#/components/responses/200"),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=401, ref="#/components/responses/401"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function updatePersonProfile(RequestUpdatePersonProfile $request) : JsonResponse {
        try {
            $service = UserService::getInstance();
            $common = EditableUserCommonDto::createFromRequest($request);
            $person = PersonProfileDto::createFromRequest($request);
            $service->updatePersonProfile($common, $person);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 인증토튼 발급을 요청한다.
     * @param RequestCertificationToken $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/user/requestCertificationToken",
     *     tags={"user"},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (
     *                  allOf={
     *                      @OA\Schema (ref="#/components/schemas/email"),
     *                      @OA\Schema (ref="#/components/schemas/certification_function")
     *                  }
     *              )
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function requestCertificationToken(RequestCertificationToken $request) : JsonResponse {
        try {
            $service = UserService::getInstance();
            $dto = RequestCertificationTokenDto::createFromRequest($request);
            $service->requestCertificationToken($dto);
            return new Message();
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 비밀번호를 초기화한다.
     * @param RequestResetPassword $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/user/resetPassword",
     *     tags={"user"},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (
     *                  allOf={
     *                      @OA\Schema (ref="#/components/schemas/email"),
     *                      @OA\Schema (ref="#/components/schemas/certification_token"),
     *                      @OA\Schema (ref="#/components/schemas/password")
     *                  }
     *              )
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function resetPassword(RequestResetPassword $request) : JsonResponse {
        try {
            $service = UserService::getInstance();
            $dto = ResetPasswordDto::createFromRequest($request);
            $service->resetPassword($dto, CertificationTokenFunction::resetPassword);
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
     * 새로운 비밀번호를 생성한다.
     * @param RequestResetPassword $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/user/createPassword",
     *     tags={"user"},
     *     @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (
     *                  allOf={
     *                      @OA\Schema (ref="#/components/schemas/email"),
     *                      @OA\Schema (ref="#/components/schemas/certification_token"),
     *                      @OA\Schema (ref="#/components/schemas/password")
     *                  }
     *              )
     *          )
     *     ),
     *     @OA\Response (response=200, ref="#/components/responses/200"),
     *     @OA\Response (response=400, ref="#/components/responses/400"),
     *     @OA\Response (response=401, ref="#/components/responses/401"),
     *     @OA\Response (response=404, ref="#/components/responses/404"),
     *     @OA\Response (response=500, ref="#/components/responses/500"),
     * )
     */
    public function createPassword(RequestResetPassword $request) : JsonResponse {
        try {
            $service = UserService::getInstance();
            $dto = ResetPasswordDto::createFromRequest($request);
            $service->resetPassword($dto, CertificationTokenFunction::createPassword);
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
     * 회원 사진을 등록하거나 변경한다.
     * @param Request $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/user/updatePhoto",
     *     tags={"user"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
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
    public function updatePhoto(Request $request) : JsonResponse {
        try {
            $service = UserService::getInstance();
            $validator = Validator::make($request->allFiles(), ['image' => ['required', 'image']]);
            if($validator->fails()) throw HttpErrorsException::getInstance($validator->getMessageBag()->toArray(), 400);
            $service->updatePhoto($request->file('image'));
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
     * 단체 회원의 약도 이미지를 등록하거나 변경한다.
     * @param Request $request
     * @return JsonResponse
     * @OA\Post (
     *     path="/user/updateRoadMap",
     *     tags={"user"},
     *     security={{"BearerAuth":{}, "AccessTokenAuth": {}}},
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
    public function updateRoadMap(Request $request) : JsonResponse {
        try {
            $service = UserService::getInstance();
            $validator = Validator::make($request->allFiles(), ['image' => ['required', 'image']]);
            if($validator->fails()) throw HttpErrorsException::getInstance($validator->getMessageBag()->toArray(), 400);
            $service->updateLoadMap($request->file('image'));
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
     * 회원 사진을 리턴한다.
     * @param User $id
     * @return mixed
     * @OA\Get (
     *     path="/user/showPhoto/{id}",
     *     tags={"user"},
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
    public function showPhoto(User $id) : mixed {
        try {
            $service = UserService::getInstance();
            return $service->showPhoto($id);
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 단체회원 약도를 리턴한다.
     * @param User $id
     * @return mixed
     * @OA\Get (
     *     path="/user/showRoadMap/{id}",
     *     tags={"user"},
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
    public function showRoadMap(User $id) : mixed {
        try {
            $service = UserService::getInstance();
            return $service->showLoadMap($id);
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }
}
