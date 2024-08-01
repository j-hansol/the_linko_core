<?php

namespace App\Services\V1;

use App\DTOs\V1\AuthInfoDto;
use App\DTOs\V1\AutoLoginDto;
use App\DTOs\V1\ChangePasswordDto;
use App\DTOs\V1\DeviceInfoDto;
use App\DTOs\V1\EditableUserCommonDto;
use App\DTOs\V1\FacebookLoginDto;
use App\DTOs\V1\OrganizationDto;
use App\DTOs\V1\PasswordLoginDto;
use App\DTOs\V1\PersonProfileDto;
use App\DTOs\V1\RequestCertificationTokenDto;
use App\DTOs\V1\ResetPasswordDto;
use App\DTOs\V1\UserCommonDto;
use App\DTOs\V1\UserTokenDto;
use App\Events\AccountCreated;
use App\Http\QueryParams\ListQueryParam;
use App\Lib\AdditionalFilter;
use App\Lib\CertificationTokenFunction;
use App\Lib\DeviceType;
use App\Lib\MemberType;
use App\Models\AccessToken;
use App\Models\CertificationToken;
use App\Models\Country;
use App\Models\Device;
use App\Models\PasswordHistory;
use App\Models\User;
use App\Models\UserType;
use App\Notifications\CertificationTokenCreated;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService {
    protected ?User $user;
    protected bool $is_logged_in = false;

    function __construct() {
        $this->user = current_user();
        $this->is_logged_in = $this->user && access_token();
    }

    /**
     * 서비스 프로바이더를 통해 인스턴스를 가져온다.
     * @return UserService
     * @throws Exception
     */
    public static function getInstance() : UserService {
        $instance = app(static::class);
        if(!$instance) throw new Exception('service not constructed');
        return $instance;
    }

    /**
     * 지정 회원 유형 목록을 리턴한다.
     * @param MemberType $type
     * @param ListQueryParam $param
     * @param AdditionalFilter|null $additional_filter
     * @return Collection
     */
    public function listByType(MemberType $type, ListQueryParam $param,
                               ?AdditionalFilter $additional_filter) : Collection {
        return User::query()
            ->select('users.*')
            ->join('user_types', 'users.id', '=', 'user_types.user_id')
            ->orderBy($param->order, $param->direction)
            ->where('user_types.type', $type->value)
            ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                $query->where($param->field, $param->operator, $param->keyword);
            })
            ->when($additional_filter, function(Builder $query) use($additional_filter) {
                $query->where($additional_filter->field, $additional_filter->op, $additional_filter->value);
            })
            ->skip($param->start_rec_no)->take($param->page_per_items)
            ->get();
    }

    /**
     * 지정 일련번호의 사용자 정보를 검색하여 콜랙션으로 리턴한다.
     * @param array|int $ids
     * @return Collection|null
     * @throws HttpException
     */
    public function findUsers(array|int $ids) : ?Collection {
        $aid = is_array($ids) ? $ids : [$ids];
        $result = User::query()
            ->whereIn('id', $aid)
            ->get();
        if($result->isEmpty()) throw HttpException::getInstance(404);
        return $result;
    }

    /**
     * 지정 단체 회원의 계약 관리 기관 계정정보를 리턴한다.
     * @param User $user
     * @return Collection|null
     * @throws HttpException
     */
    public function getManagers(User $user) : ?Collection {
        $ids = DB::table('managers')
            ->where('organization_user_id', $user->id)
            ->get()->pluck('manager_user_id')->toArray();
        if(empty($ids)) return null;;
        return $this->findUsers($ids);
    }

    /**
     * 지정 사용자가 해당 단체의 관리기관인지 여부를 판단한다.
     * @param User $organization
     * @param User $user
     * @return bool
     */
    public function isManager(User $organization, User $user) : bool {
        return (DB::table('managers')
            ->where('organization_user_id', $user->id)
            ->where('manager_user_id', $user->id)
            ->count()) > 0;
    }

    /**
     * 지정 국가의 대표 관리기관 계정 정보를 리턴한다.
     * @param Country $country
     * @return User|null
     * @throws HttpException
     */
    public function getManagerByCountry(Country $country) :?User {
        $result = User::orderBy('users.id', 'asc')
            ->select('users.*')
            ->join('user_types', 'users.id', '=', 'user_types.user_id')
            ->where('user_types.type', MemberType::TYPE_FOREIGN_MANAGER->value)
            ->where('users.country_id', $country->id)
            ->where('users.active', '1')
            ->take(1)->get()->first();
        if(!$result) throw HttpException::getInstance(404);
        return $result;
    }

    /**
     * 개인회원 가입을 처리한다.
     * @param UserCommonDto $common
     * @param PersonProfileDto $person
     * @param AuthInfoDto $auth
     * @param DeviceInfoDto $device
     * @return UserTokenDto
     * @throws HttpException|HttpErrorsException
     */
    public function joinPerson(
        UserCommonDto $common, PersonProfileDto $person, AuthInfoDto $auth, DeviceInfoDto $device) : UserTokenDto {
        if(!$this->user) {
            $id_info = User::genInitialTemporaryIdAlias($common->getType()->value);
            $t = [
                'api_token' => User::genApiToken(),
                'active' => $common->getType() == MemberType::TYPE_FOREIGN_PERSON
            ];
            $user = User::create($id_info + $common->toArray() + $person->toArray() + $auth->toArray() + $t);
            if($auth->getHashedPassword()) PasswordHistory::createByUser($user, $auth->getHashedPassword());
            UserType::createType($user, $common->getType());

            $device = match ($device->getDeviceType()) {
                DeviceType::TYPE_MOBILE => Device::createMobileDevice(
                    $user, $device->getDeviceName(), $device->getUUID(), $device->getFcmToken()),
                DeviceType::TYPE_FIXED => Device::createFixedDevice($user)
            };
            $token = AccessToken::createByUserData($user, $device, true);
            if($common->getType() != MemberType::TYPE_FOREIGN_PERSON) AccountCreated::dispatch($user);
            return new UserTokenDto($user, $token);
        }
        else throw HttpErrorsException::getInstance([__('errors.user.already_exists')], 406);
    }

    /**
     * 단체회원 가입을 처리한다.
     * @param UserCommonDto $common
     * @param OrganizationDto $organization
     * @param AuthInfoDto $auth
     * @param DeviceInfoDto $device
     * @return UserTokenDto
     * @throws HttpException|HttpErrorsException
     */
    public function joinOrganization(
        UserCommonDto $common, OrganizationDto $organization, AuthInfoDto $auth, DeviceInfoDto $device) : UserTokenDto {
        if(!$this->user) {
            $id_info = User::genInitialTemporaryIdAlias($common->getType()->value);
            $t = [
                'api_token' => User::genApiToken(),
                'active' => 0
            ];
            $user = User::create($id_info + $common->toArray() + $organization->toArray() + $auth->toArray() + $t);
            if($auth->getHashedPassword()) PasswordHistory::createByUser($user, $auth->getHashedPassword());
            UserType::createType($user, $common->getType());
            if($common->getType() == MemberType::TYPE_GOVERNMENT)
                UserType::createType($user, MemberType::TYPE_ORDER);
            elseif($common->getType() == MemberType::TYPE_FOREIGN_GOVERNMENT)
                UserType::createType($user, MemberType::TYPE_RECIPIENT);
            elseif($common->getType() == MemberType::TYPE_FOREIGN_PROVIDER) {
                UserType::createType($user, MemberType::TYPE_RECIPIENT);
                UserType::createType($user, MemberType::TYPE_FOREIGN_MANAGER);
                UserType::createType($user, MemberType::TYPE_FOREIGN_MANAGER_OPERATOR);
            }

            $device = match ($device->getDeviceType()) {
                DeviceType::TYPE_MOBILE => Device::createMobileDevice(
                    $user, $device->getDeviceName(), $device->getUUID(), $device->getFcmToken()),
                DeviceType::TYPE_FIXED => Device::createFixedDevice($user)
            };
            $token = AccessToken::createByUserData($user, $device, true);
            AccountCreated::dispatch($user);
            return new UserTokenDto($user, $token, $user->active == 1 ? 200 : 201);
        }
        else throw HttpErrorsException::getInstance([__('errors.user.already_exists')], 406);
    }

    /**
     * 별칭과 비밀번호를 이용한 로그인 처리를 한다.
     * @param PasswordLoginDto $dto
     * @return UserTokenDto
     * @throws HttpException|HttpErrorsException
     */
    public function login(PasswordLoginDto $dto) : UserTokenDto {
        if(!$this->user) {
            $user = User::findByIdAlias($dto->getIdAlias());
            if($user && Hash::check($dto->getPassword(), $user->password)) {
                if($dto->getDeviceType() == DeviceType::TYPE_FIXED) {
                    // $device = Device::getDeviceByUser( $user, DeviceType::TYPE_FIXED );
                    $device = Device::findByCurrentIpAddress($user, DeviceType::TYPE_FIXED);
                    if(!$device) $device = Device::createFixedDevice($user);
                    $token = AccessToken::createByUserData($user, $device, true);
                    return new UserTokenDto($user, $token);
                }
                else {
                    $device = Device::findByUUID($dto->getUUID());
                    if(!$device) {
                        $device = Device::createMobileDevice(
                            $user, $dto->getUUID(), $dto->getFCMToken());
                        $valid_device = true;
                    }
                    elseif($device && $device->user_id == $user->id) {$valid_device = true;}
                    else throw HttpException::getInstance(401);

                    $device->touch();
                    $token = AccessToken::createByUserData($user, $device, $valid_device);
                    return new UserTokenDto($user, $token, $valid_device ? 200 : 205);
                }
            }
            else throw HttpException::getInstance(401);
        }
        else throw HttpErrorsException::getInstance([__('errors.user.already_exists')], 406);
    }

    /**
     * 페이스북 로그인 처리를 진행한다.
     * @param FacebookLoginDto $dto
     * @return UserTokenDto
     * @throws HttpException|HttpErrorsException
     */
    public function loginByFacebook(FacebookLoginDto $dto) : UserTokenDto {
        if(!$this->user) {
            $user = User::findByEmail($dto->getEmail());
            if($user) {
                if($user->auth_provider == $dto->getAuthProvider() &&
                    $user->auth_provider_identifier == $dto->getAuthProviderIdentifier()) {
                    if($dto->getDeviceType() == DeviceType::TYPE_FIXED) {
                        $device = Device::getDeviceByUser( $user, DeviceType::TYPE_FIXED );
                        if(!$device) $device = Device::createFixedDevice($user);
                        $token = AccessToken::createByUserData($user, $device);
                        return new UserTokenDto($user, $token);
                    }
                    else {
                        $device = Device::getDeviceByUser( $user, DeviceType::TYPE_MOBILE );
                        $valid_device = false;
                        if(!$device) {
                            $device = Device::createMobileDevice(
                                $user, $dto->getDeviceName(), $dto->getUUID(), $dto->getFCMToken());
                            $valid_device = true;
                        }
                        elseif($device->uuid = $dto->getUUID()) $valid_device = true;
                        $token = AccessToken::createByUserData($user, $device, $valid_device);
                        return new UserTokenDto($user, $token, $valid_device ? 200 : 205);
                    }
                }
                else throw HttpException::getInstance(401);
            }
        }
        else throw HttpErrorsException::getInstance([__('errors.user.already_exists')], 406);
    }

    /**
     * 자동 로그인을 처리한다.
     * @param AutoLoginDto $dto
     * @return UserTokenDto
     * @throws HttpException
     */
    public function loginAuto(AutoLoginDto $dto) : UserTokenDto {
        if($this->user && !access_token()) {
            if($dto->getDeviceType() == DeviceType::TYPE_FIXED) {
                $device = Device::getDeviceByUser( $this->user, DeviceType::TYPE_FIXED );
                if($device) $device = Device::createFixedDevice($this->user);
                $token = AccessToken::createByUserData($this->user, $device);
                return new UserTokenDto($this->user, $token);
            }
            else {
                $device = Device::getDeviceByUser($this->user, DeviceType::TYPE_MOBILE );
                $valid_device = false;
                if(!$device) {
                    $device = Device::createMobileDevice(
                        $this->user, $dto->getDeviceName(), $dto->getUUID(), $dto->getFCMToken());
                    $valid_device = true;
                }
                elseif($device->uuid == $dto->getUUID()) $valid_device = true;
                $token = AccessToken::createByUserData($this->user, $device, $valid_device);
                return new UserTokenDto($this->user, $token, $valid_device ? 200 : 205);
            }
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 로그아웃 처리를 한다.
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function logout() : void {
        if($this->is_logged_in) {
            $token = AccessToken::findMe(access_token());
            if($token) $token->delete();
            else throw HttpErrorsException::getInstance([__('errors.user.no_token')], 406);
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 로그인 사용자의 계정정보를 리턴한다.
     * @return User|null
     * @throws HttpException
     */
    public function getMyInfo() : ?User {
        if($this->is_logged_in) return $this->user;
        else throw HttpException::getInstance(401);
    }

    /**
     * 단말기 정보를 변경한다.
     * @param DeviceInfoDto $dto
     * @return void
     * @throws HttpException
     */
    public function updateDevice(DeviceInfoDto $dto) : void {
        if($this->user) {
            $device = Device::getDeviceByUser($this->user, DeviceType::TYPE_MOBILE);
            if(!$device) Device::createMobileDevice(
                $this->user, $dto->getDeviceName(), $dto->getUUID(), $dto->getFcmToken());
            else {
                $device->fill($dto->toArray());
                $device->save();
            }
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 로그인 사용자의 비밀번호를 변경한다.
     * @param ChangePasswordDto $dto
     * @return void
     * @throws HttpException
     */
    public function changePassword(ChangePasswordDto $dto) : void {
        if($this->user && Hash::check($dto->getCurrentPassword(), $this->user->password)) {
            $this->user->password = $dto->getHashedPassword();
            $this->user->save();
        }
        else throw HttpException::getInstance(401);;
    }

    /**
     * FCM 토큰을 등록한다.
     * @param string $fcm_token
     * @return void
     * @throws HttpException
     */
    public function updateFCMToken(string $fcm_token) : void {
        if($this->user && access_token()) {
            $access_token = AccessToken::findMe(access_token());
            if($access_token) {
                $device = Device::findByUUID($access_token->uuid);
                if($device) {
                    $device->fcm_token = $fcm_token;
                    $device->save();
                }
                else throw HttpException::getInstance(401);
            }
            else throw HttpException::getInstance(401);
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * id 별칭을 이용하여 사용자 정보를 리턴한다.
     * @param string $id_alias
     * @return User|null
     */
    public function getIdByIdAlias(string $id_alias) : ?User {
        return User::findByIdAlias($id_alias);
    }

    /**
     * 회원의 단체정보를 업데이트한다.
     * @param UserCommonDto $common
     * @param OrganizationDto $organization
     * @return void
     * @throws HttpException
     */
    public function updateOrganizationProfile(EditableUserCommonDto $common, OrganizationDto $organization) : void {
        if($this->user && $this->user->isOwnOrganizationType()) {
            $this->user->fill($common->toArray() + $organization->toArray());
            $this->user->save();
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 회원의 개인 프로필 정보를 업데이트한다.
     * @param EditableUserCommonDto $common
     * @param PersonProfileDto $person
     * @return void
     * @throws HttpException
     */
    public function updatePersonProfile(EditableUserCommonDto $common, PersonProfileDto $person) : void {
        if($this->user && $this->user->isOwnPersonType()) {
            $this->user->fill($common->toArray() + $person->toArray());
            $this->user->save();
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 기능 수행을 위한 토큰을 요청한다.
     * @param RequestCertificationTokenDto $dto
     * @return void
     * @throws HttpErrorsException
     */
    public function requestCertificationToken(RequestCertificationTokenDto $dto) : void {
        $user = User::findByEmail($dto->getEmail());
        if($user) {
            $token = CertificationToken::createToken($user, $dto->getFunction()->value);
            $user->notify(new CertificationTokenCreated($user, $token));
        }
        else throw HttpErrorsException::getInstance([__('errors.user.not_found')], 400);
    }

    /**
     * 비밀번호를 변경하거나 생성한다.
     * @param ResetPasswordDto $dto
     * @param CertificationTokenFunction $function
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function resetPassword(ResetPasswordDto $dto, CertificationTokenFunction $function) : void {
        $user = User::findByEmail($dto->getEmail());
        if($user) {
            $token = CertificationToken::getToken($user, $function->value, $dto->getToken());
            if($token) {
                $user->password = $dto->getHashedPassword();
                $user->save();
                PasswordHistory::createByUser($user, $user->password);
                $token->delete();
            }
            else throw HttpErrorsException::getInstance([__('errors.user.not_found')], 406);
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 회원 사진을 변경한다.
     * @param UploadedFile $file
     * @return void
     * @throws HttpException
     */
    public function updatePhoto(UploadedFile $file) : void {
        if($this->user) {
            $path = User::savePhoto($file);
            $this->user->updatePhoto($path);
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 회원(단체)의 약도 이미지를 변경한다.
     * @param UploadedFile $file
     * @return void
     * @throws HttpException
     */
    public function updateLoadMap(UploadedFile $file) : void {
        if($this->user) {
            $path = User::saveRoadMap($file);
            $this->user->updateRoadMap($path);
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 회원 사진을 출력한다.
     * @param User $user
     * @return mixed
     * @throws HttpException
     */
    public function showPhoto(User $user) : mixed {
        if ($user->photo) return show_file('local', $user->photo);
        else throw HttpException::getInstance(404);
    }

    /**
     * 회원(단체) 약도 이미지를 출력한다.
     * @param User $user
     * @return mixed
     * @throws HttpException
     */
    public function showLoadMap(User $user) : mixed {
        if ($user->road_map) return show_file('local', $user->road_map);
        else throw HttpException::getInstance(404);
    }
}
