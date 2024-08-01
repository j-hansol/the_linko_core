<?php

namespace App\Services\V1;

use App\DTOs\V1\ExcelImporterDto;
use App\DTOs\V1\IWorkerPassportDto;
use App\DTOs\V1\PreSaveWorkerDto;
use App\DTOs\V1\PreSaveWorkerFromExcelDto;
use App\DTOs\V1\VisaDocumentDto;
use App\DTOs\V1\WorkerAccountDto;
use App\DTOs\V1\WorkerEducationDto;
use App\DTOs\V1\WorkerEtcExperienceFileDto;
use App\DTOs\V1\WorkerExperienceDto;
use App\DTOs\V1\WorkerFamilyDto;
use App\DTOs\V1\WorkerInfoDto;
use App\DTOs\V1\WorkerPassportFileDto;
use App\DTOs\V1\WorkerResumeDto;
use App\DTOs\V1\WorkerVisaDocumentsDto;
use App\DTOs\V1\WorkerVisitCountryDto;
use App\Events\AccountCreated;
use App\Http\QueryParams\CountryParam;
use App\Http\QueryParams\ListQueryParam;
use App\Imports\PreSaveWorkerImporter;
use App\Lib\LoginMethod;
use App\Lib\MemberType;
use App\Lib\PageCollection;
use App\Models\Device;
use App\Models\PasswordHistory;
use App\Models\PreSaveWorkerInfo;
use App\Models\User;
use App\Models\UserType;
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
use App\Traits\Common\CreateWorkerAccount;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkerManagementService {
    use CreateWorkerAccount;

    protected ?User $user;
    protected ?User $manager;

    public function __construct() {
        $this->user = current_user();
        $this->manager = $this->user->getAffiliationManager();
    }

    /**
     * 현재 로그인 사용자의 소속 기관 계정정보를 리턴한다.
     * @return User|null
     */
    public function getManager() :?User {return $this->manager;}

    /**
     * 근로자 관리용 서비서 객체를 리턴한다.
     * @return WorkerManagementService
     * @throws Exception
     */
    public static function getInstance() : WorkerManagementService {
        $instance = app(static::class);
        if(!$instance) throw new Exception('service not constructed');
        return $instance;
    }

    /**
     * 관리중인 소ㅛ속 근로자 여부를 판단한다.
     * @param User $user
     * @return bool
     */
    private function isManagedWorker(User $user) : bool {
        return $this->manager->id == $user->management_org_id;
    }

    /**
     * 해당 근로자 임시 데이터가 관리중인 데이터인지 여부를 판단한다.
     * @param PreSaveWorkerInfo $info
     * @return bool
     */
    private function isManagedPreSavedWorker(PreSaveWorkerInfo $info) : bool {
        return $this->manager->id == $info->management_org_id;
    }

    /**
     * 관리중인 소속 근로자 목록을 리턴한다.
     * @param ListQueryParam $param
     * @param CountryParam $country
     * @return PageCollection
     * @throws HttpException
     */
    public function listWorker(ListQueryParam $param, CountryParam $country) : PageCollection {
        $query = User::orderBy($param->order, $param->direction)
            ->select('users.*')
            ->join('user_types', 'users.id', '=', 'user_types.user_id')
            ->when($country->country, function(Builder $query) use ($country) {
                $query->where('country_id', $country->country->id);
            })
            ->where('is_organization', 0)
            ->where('users.management_org_id', $this->manager->id)
            ->where('user_types.type', MemberType::TYPE_FOREIGN_PERSON->value)
            ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                $query->where($param->field, $param->operator, $param->keyword);
            });
        $total = $query->count();
        $total_page = ceil($total / $param->page_per_items);
        $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
            ->get();
        return new PageCollection($total, $total_page, $collection);
    }

    /**
     * 소속 근로자 계정을 생성한다.
     * @param WorkerAccountDto $dto
     * @return void
     * @throws Exception
     */
    public function joinWorker(WorkerAccountDto $dto) : void {
        DB::beginTransaction();
        try {
            $initial_user_type = MemberType::TYPE_FOREIGN_PERSON;
            $id_info = User::genInitialTemporaryIdAlias($initial_user_type->value);
            $hashed_password = $dto->getHashedPassword();
            $etc = [
                'api_token' => User::genApiToken(),
                'login_method' => LoginMethod::LOGIN_METHOD_PASSWORD->value,
                'active' => 1,
                'is_organization' => 0,
                'management_org_id' => $this->manager->id,
            ];
            $profile = $dto->toArray();

            $user = User::create($id_info + $profile + $etc);
            if($user instanceof User) {
                $user->createInitialPassword($this->manager, $dto->getPassword());
                PasswordHistory::createByUser($user, $hashed_password);
                UserType::createType($user, $initial_user_type);
                Device::createFixedDevice($user);
            }
            DB::commit();
            AccountCreated::dispatch($user);
        }
        catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 배열의 정보를 이용하여 소속 근로자 계정을 생성한다.
     * @param array $profile
     * @return User|null
     * @throws Exception
     */
    public function joinWorkerFromArray(array $profile) : ?User {
        DB::beginTransaction();
        try {
            $initial_user_type = MemberType::TYPE_FOREIGN_PERSON;
            $id_info = User::genInitialTemporaryIdAlias($initial_user_type->value);
            $password = Str::random(12);
            $hashed_password = Hash::make($password);
            $etc = [
                'api_token' => User::genApiToken(),
                'login_method' => LoginMethod::LOGIN_METHOD_PASSWORD->value,
                'active' => 1,
                'is_organization' => 0,
                'management_org_id' => $this->manager->id,
                'password' => $hashed_password
            ];

            $user = User::create($id_info + $profile + $etc);
            if($user instanceof User) {
                $user->createInitialPassword($this->manager, $password);
                PasswordHistory::createByUser($user, $hashed_password);
                UserType::createType($user, $initial_user_type);
                Device::createFixedDevice($user);
            }
            DB::commit();
            AccountCreated::dispatch($user);
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 근로자 계정 정보를 수정한다.
     * @param WorkerAccountDto $dto
     * @param User $user
     * @return void
     * @throws Exception|HttpException
     */
    public function updateWorker(WorkerAccountDto $dto, User $user) : void {
        DB::beginTransaction();
        try {
            $user->fill($dto->toArray());
            $user->save();
            $hashed_password = $dto->getHashedPassword();
            if($hashed_password) PasswordHistory::createByUser($user, $dto->getHashedPassword());
            DB::commit();
        }
        catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 회원 사진을 변경한다.
     * @param UploadedFile $file
     * @param User $user
     * @return void
     */
    public function updatePhoto(UploadedFile $file, User $worker) : void {
        $path = User::savePhoto($file);
        $worker->updatePhoto($path);
    }

    /**
     * 소속 근로자의 초기 비밀번호를 리턴한다.
     * @param User $user
     * @return string|null
     * @throws HttpException
     */
    public function getInitialPassword(User $user) : ?string {
        if($this->isManagedWorker($user)) return $user->getInitialPassword();
        else throw HttpException::getInstance(403);
    }

    /**
     * 관리중인 소속 근로자를 관리를 중지한다.
     * @param User $user
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function cancelManaging(User $user) : void {
        if($this->isManagedWorker($user)) {
            if($user->isOwnType(MemberType::TYPE_FOREIGN_PERSON)) {
                $user->management_org_id = null;
                $user->save();
            }
            else throw HttpErrorsException::getInstance([__('errors.management.no_target')], 406);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 임시 저장된 근로자 정보 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listPreSavedWorker(ListQueryParam $param) : PageCollection {
         $query = PreSaveWorkerInfo::orderBy($param->order, $param->direction)
            ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                $query->where($param->field, $param->operator, $param->keyword);
            })
            ->where('management_org_id', $this->manager->id);
         $total = $query->count();
        $total_page = ceil($total / $param->page_per_items);
        $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
             ->get();
         return new PageCollection($total, $total_page, $collection);
    }

    /**
     * 소속 근로자 정보를 임시저장한다. 저장 시 가능한 경우 계정 생성도 가능하다.
     * @param PreSaveWorkerDto $dto
     * @return void
     * @throws Exception
     */
    public function preSaveWorker(PreSaveWorkerDto $dto) : void {
        DB::beginTransaction();
        $user = null;
        try {
            if($dto->getCreateAccount() && $dto->isCreatable())
                $user = $this->_createAccount($dto, $this->manager, MemberType::TYPE_FOREIGN_PERSON);
            $this->_createPreSaveWorkerInfo($dto, $this->manager, $user);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 엑셀 파일로부터 소속 근로자 정보를 임시 저장한다.
     * @param PreSaveWorkerFromExcelDto $dto
     * @return ExcelImporterDto|null
     */
    public function preSaveWorkerFromExcel(PreSaveWorkerFromExcelDto $dto) : ?ExcelImporterDto {
        $importer = new PreSaveWorkerImporter($this->manager, $dto->getCreateAccount());
        Excel::import($importer, $dto->getWorkFilePath());
        $dto->unLink();
        return new ExcelImporterDto($importer->total, $importer->success, $importer->errors);
    }

    /**
     * 소속 근로자 임시 데이터를 변경한다.
     * @param PreSaveWorkerDto $dto
     * @param PreSaveWorkerInfo $info
     * @return void
     * @throws Exception
     */
    public function updatePreSavedWorker(PreSaveWorkerDto $dto, PreSaveWorkerInfo $info) : void {
        if($this->isManagedPreSavedWorker($info)) {
            DB::beginTransaction();
            try {
                $user = null;
                if($dto->getCreateAccount() && $dto->isCreatable() && !$info->user_id)
                    $user = $this->_createAccount($dto, $this->manager, MemberType::TYPE_FOREIGN_PERSON);
                $this->_updatePreSaveWorkerInfo($dto, $info, $user);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
        else throw new HttpException(403);
    }

    /**
     * 소속 근로자 임시 데이터를 삭제한다.
     * @param PreSaveWorkerInfo $info
     * @return void
     * @throws HttpException
     */
    public function deletePreSaveWorker(PreSaveWorkerInfo $info) : void {
        if($this->isManagedPreSavedWorker($info)) $info->delete();
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자 신체정보를 리턴한다.
     * @param User $user
     * @return WorkerInfo
     * @throws HttpException
     */
    public function getInfo(User $user) : WorkerInfo {
        if($this->user->isMe($user) || $this->isManagedWorker($user)) {
            $info = WorkerInfo::findByUser($user);
            if(!$info) throw HttpException::getInstance(404);
            return $info;
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자 신체정보를 리턴한다. 없는 경우 Null을 리턴한다.
     * @param User $user
     * @return WorkerInfo|null
     * @throws HttpException
     */
    public function getInfoOrNull(User $user) : ?WorkerInfo {
        if($this->user->isMe($user) || $this->isManagedWorker($user)) {
            $info = WorkerInfo::findByUser($user);
            return $info;
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자 정보를 설정한다.
     * @param WorkerInfoDto $dto
     * @param User $user
     * @return void
     * @throws HttpException
     */
    public function setInfo(WorkerInfoDto $dto, User $user) : void {
        if($this->user->isMe($user) || $this->isManagedWorker($user)) {
            $info = WorkerInfo::findByUser($user);
            if($info) {
                $info->fill($dto->toArray());
                $info->save();
            }
            else WorkerInfo::create([
                'user_id' => $user->id
            ] + $dto->toArray());
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 지정 근로자의 방문국가정보 목록을 리턴한다.
     * @param User $user
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listVisitedCountry(User $user, ListQueryParam $param) : PageCollection {
        if($this->user->isMe($user) || $this->isManagedWorker($user)) {
            $query = WorkerVisit::orderBy($param->order, $param->direction)
                ->where('user_id', $user->id)
                ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                });
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $collection);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 국가 방문정보를 리턴한다.
     * @param WorkerVisit $info
     * @return WorkerVisit|null
     * @throws HttpException
     */
    public function getVisitedCountry(WorkerVisit $info) : ?WorkerVisit {
        $user = User::findMe($info->user_id);
        if($this->user->isMe($user) || $this->isManagedWorker($user)) return $info;
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 국가 방문정보를 등록한다.
     * @param WorkerVisitCountryDto $dto
     * @param User $user
     * @return void
     * @throws HttpException
     */
    public function addVisitedCountry(WorkerVisitCountryDto $dto, User $user) : void {
        if($this->user->isMe($user) || $this->isManagedWorker($user)) {
            WorkerVisit::create([
                'user_id' => $user->id
            ] + $dto->toArray());
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 기존 방문정보를 수정한다.
     * @param WorkerVisitCountryDto $dto
     * @param WorkerVisit $visit
     * @return void
     * @throws HttpException
     */
    public function updateVisitedCountry(WorkerVisitCountryDto $dto, WorkerVisit $visit) : void {
        $user = User::findMe($visit->user_id);
        if ($this->user->isMe($user) || $this->isManagedWorker($user)) {
            $visit->fill($dto->toArray());
            $visit->save();
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 방문국가정보를 삭제한다.
     * @param WorkerVisit $visit
     * @return void
     * @throws HttpException
     */
    public function deleteVisitedCountry(WorkerVisit $visit) : void {
        $user = User::findMe($visit->user_id);
        if ($this->user->isMe($user) || $this->isManagedWorker($user)) {
            if($visit->reference_count == 0) $visit->delete();
            else throw HttpErrorsException::getInstance([__('errors.auth.no_permission')], 406);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 가족정보를 리턴한다.
     * @param User $user
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listFamily(User $user, ListQueryParam $param) : PageCollection {
        if($this->user->isMe($user) || $this->isManagedWorker($user)) {
            $query = WorkerFamily::orderBy($param->order, $param->direction)
                ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                    $query->where($param->field, $param->operator, $param);
                })
                ->when($user, function(Builder $query) use($user) {
                    $query->where('user_id', $user->id);
                });
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $collection);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 가족정보를 리턴한다.
     * @param WorkerFamily $family
     * @return WorkerFamily
     * @throws HttpException
     */
    public function getFamily(WorkerFamily $family) : WorkerFamily {
        $user = User::findMe($family->user_id);
        if ($this->user->isMe($user) || $this->isManagedWorker($user)) return $family;
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 가족정보를 등록한다.
     * @param WorkerFamilyDto $dto
     * @param User $user
     * @return void
     * @throws HttpException
     */
    public function addFamily(WorkerFamilyDto $dto, User $user) : void {
        if($this->user->isMe($user) || $this->isManagedWorker($user)) {
            WorkerFamily::create([
                'user_id' => $user->id
            ] + $dto->toArray());
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 지정 근로자 가족정보를 수정한다.
     * @param WorkerFamilyDto $dto
     * @param WorkerFamily $family
     * @return void
     * @throws HttpException
     */
    public function updateFamily(WorkerFamilyDto $dto, WorkerFamily $family) : void {
        $user = User::findMe($family->user_id);
        if($this->user->isMe($user) || $this->isManagedWorker($user)) {
            $family->fill($dto->toArray());
            $family->save();
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 지정 근로자 가족정보를 삭제한다.
     * @param WorkerFamily $family
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function deleteFamily(WorkerFamily $family) : void {
        $user = User::findMe($family->user_id);
        if ($this->user->isMe($user) || $this->isManagedWorker($user)) {
            if($family->reference_count == 0) $family->delete();
            else throw HttpErrorsException::getInstance([__('errors.auth.no_permission')], 406);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 여권정보를 등록한다. 만일 기존 등록된 여권정보인 경우 기존 여권정보를 전달한다.
     * @param IWorkerPassportDto $dto
     * @param User $worker
     * @return WorkerPassport|null
     * @throws HttpException
     */
    public function addPassport(IWorkerPassportDto $dto, User $worker) : ?WorkerPassport {
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            $passport = WorkerPassport::where('user_id', $worker->id)
                ->where('passport_no', $dto->getPassportNo())
                ->get()->first();
            if($passport) {
                Log::info('Passport already exists', $passport->toArray());
                telegram_message('해당 여권정보는 이미 등록되어 있습니다.');
                return $passport;
            }
            else {
                $passport = WorkerPassport::create([
                        'user_id' => $worker->id
                    ] + $dto->toArray());
                Log::info('Passport created', $passport->toArray());
                telegram_message('여권 정보가 등록되었습니다.');
                return $passport;
            }
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 여권정보를 변경한다.
     * @param IWorkerPassportDto $dto
     * @param WorkerPassport $passport
     * @return void
     * @throws HttpException
     */
    public function updatePassport(IWorkerPassportDto $dto, WorkerPassport $passport) : void {
        $worker = User::findMe($passport->user_id);
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            $passport->fill($dto->toArray());
            $passport->save();
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 여권정보를 삭제한다.
     * @param WorkerPassport $passport
     * @return void
     * @throws HttpException
     */
    public function deletePassport(WorkerPassport $passport) : void {
        $worker = User::findMe($passport->user_id);
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) $passport->delete();
        else throw HttpException::getInstance(403);
    }

    /**
     * 여권 이미지 또는 파일을 등록한다.
     * @param WorkerPassportFileDto $dto
     * @param WorkerPassport $passport
     * @return void
     */
    public function setPassportFile(WorkerPassportFileDto $dto, WorkerPassport $passport) : void {
        $worker = User::findMe($passport->user_id);
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            if($passport->file_path) Storage::disk('local')->delete($passport->file_path);
            $passport->file_path = $dto->getFilePath();
            $passport->save();
        }
        else HttpException::getInstance(403);
    }

    /**
     * 근로자 본인 및 관리기관, 관리기관 실무자에게 여권 목록을 리턴한다.
     * @param ListQueryParam $param
     * @param User $worker
     * @return PageCollection
     * @throws HttpException
     */
    public function listPassportForWorker(ListQueryParam $param, User $worker) : PageCollection {
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            $query = WorkerPassport::orderBy($param->order, $param->direction)
                ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                })
                ->where('user_id', $worker->id);
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $collection);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 여권정보를 리턴한다.
     * @param WorkerPassport $passport
     * @return WorkerPassport|null
     */
    public function getPassport(WorkerPassport $passport) : ?WorkerPassport {
        $worker = User::findMe($passport->user_id);
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) return $passport;
        else return null;
    }

    /**
     * 근로자 본인 또는 관기기관 및 관리기관 실무자에게 여권 파일을 출력한다.
     * @param WorkerPassport $passport
     * @return StreamedResponse
     * @throws HttpException
     */
    public function showPassportFile(WorkerPassport $passport) : StreamedResponse {
        $worker = User::findMe($passport->user_id);
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            if($passport->file_path) return show_file('local', $passport->file_path);
            else throw HttpException::getInstance(404);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 비자신청 서류 목록을 리턴한다.
     * @param User $worker
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listDocument(User $worker, ListQueryParam $param) : PageCollection {
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            $query = WorkerVisaDocument::orderBy($param->order, $param->direction)
                ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                })
                ->where('user_id', $worker->id);
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $collection);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 비자신청 문서를 등록한다.
     * @param VisaDocumentDto $dto
     * @param User $worker
     * @return void
     * @throws HttpException
     */
    public function addDocument(VisaDocumentDto $dto, User $worker) : void {
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            WorkerVisaDocument::create([
                    'user_id' => $worker->id
                ] + $dto->toArray());
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 여러 개의 문서를 등록한다.
     * @param WorkerVisaDocumentsDto $dto
     * @param User $worker
     * @return void
     * @throws HttpException
     */
    public function addDocuments(WorkerVisaDocumentsDto $dto, User $worker) : void {
        try {
            if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
                $infos = $dto->toArray();
                foreach ($infos as $info) {
                    WorkerVisaDocument::create([
                            'user_id' => $worker->id
                        ] + $info);
                }
            }
            else throw HttpException::getInstance(403);
        }
        catch (Exception $e) {
            $dto->deleteFiles();
            throw $e;
        }
    }

    /**
     * 비자신청 문서정보를 변경한다.
     * @param VisaDocumentDto $dto
     * @param WorkerVisaDocument $document
     * @return void
     * @throws HttpException
     */
    public function updateDocument(VisaDocumentDto $dto, WorkerVisaDocument $document) : void {
        $worker = User::findMe($document->user_id);
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            if($document->file_path && $dto->getFile()) {
                Storage::disk('local')->delete($document->file_path);
                $document->file_path = null;
            }
            $document->fill($dto->toArray());
            $document->save();
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 비자신청 문서를 삭제한다.
     * @param WorkerVisaDocument $document
     * @return void
     * @throws HttpException
     */
    public function deleteDocument(WorkerVisaDocument $document) : void {
        $worker = User::findMe($document->user_id);
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) $document->delete();
        else throw HttpException::getInstance(403);
    }

    /**
     * 문서 내용을 출력한다.
     * @param WorkerVisaDocument $document
     * @return mixed
     */
    public function showDocumentFile(WorkerVisaDocument $document) : mixed {
        return show_file('local', $document->file_path);
    }

    /**
     * 지정 근로자의 이력서 목록을 리턴한다.
     * @param User $worker
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listWorkerResume(User $worker, ListQueryParam $param) : PageCollection {
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            $query = WorkerResume::orderBy($param->order, $param->direction)
                ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                })
                ->where('user_id', $worker->id);
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $collection);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자 이력서를 등록한다.
     * @param User $worker
     * @param WorkerResumeDto $dto
     * @return void
     * @throws HttpException
     */
    public function addWorkerResume(User $worker, WorkerResumeDto $dto) : void {
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            WorkerResume::create([
                'user_id' => $worker->id,
                'write_user_id' => $this->user->id
            ] + $dto->toArray());
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 이력서를 변경한다.
     * @param WorkerResume $resume
     * @param WorkerResumeDto $dto
     * @return void
     * @throws HttpException
     */
    public function updateWorkerResume(WorkerResume $resume, WorkerResumeDto $dto) : void {
        $worker = User::findMe($resume->user_id);
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            if($resume->file_path && $dto->getFilePath())
                Storage::disk('local')->delete($resume->file_path);
            $resume->fill($dto->toArray());
            $resume->save();
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 이력서를 삭제한다.
     * @param WorkerResume $resume
     * @return void
     * @throws HttpException
     */
    public function deleteWorkerResume(WorkerResume $resume) : void {
        $worker = User::findMe($resume->user_id);
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) $resume->delete();
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 이력서 파일 내용을 출력한다.
     * @param WorkerResume $resume
     * @return mixed
     */
    public function showWorkerResumeFile(WorkerResume $resume) : mixed {
        return show_file('local', $resume->file_path);
    }

    /**
     * 지정 근로자의 경력정보 목록을 리턴한다.
     * @param User $worker
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listWorkerExperience(User $worker, ListQueryParam $param) : PageCollection {
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            $query = WorkerExperience::orderBy($param->order, $param->direction)
                ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                })
                ->where('user_id', $worker->id);
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $collection);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 경력사항을 등록한다.
     * @param User $worker
     * @param WorkerExperienceDto $dto
     * @return void
     * @throws HttpException
     */
    public function addWorkerExperience(User $worker, WorkerExperienceDto $dto) : void {
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            WorkerExperience::create([
                'user_id' => $worker->id,
                'write_user_id' => $this->user->id
            ] + $dto->toArray());
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 경력사항을 변경한다.
     * @param WorkerExperience $experience
     * @param WorkerExperienceDto $dto
     * @return void
     * @throws HttpException
     */
    public function updateWorkerExperience(WorkerExperience $experience, WorkerExperienceDto $dto) : void {
        $worker = User::findMe($experience->user_id);
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            if($experience->file_path && $dto->getFilePath())
                Storage::disk('local')->delete($experience->file_path);
            $experience->fill($dto->toArrayForUpdate());
            $experience->save();
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 경력사항을 삭제한다.
     * @param WorkerExperience $experience
     * @return void
     * @throws HttpException
     */
    public function deleteWorkerExperience(WorkerExperience $experience) : void {
        $worker = User::findMe($experience->user_id);
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) $experience->delete();
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 경력사항을 리텬한다.
     * @param WorkerExperience $experience
     * @return WorkerExperience
     * @throws HttpException
     */
    public function getWorkerExperience(WorkerExperience $experience) : WorkerExperience {
        $worker = User::findMe($experience->user_id);
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) return $experience;
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 경력증명서를 출력한다.
     * @param WorkerExperience $experience
     * @return mixed
     */
    public function showWorkerExperienceFile(WorkerExperience $experience) : mixed {
        return show_file('local', $experience->file_path);
    }

    /**
     * 지정 근로자의 이력서 목록을 리턴한다.
     * @param User $worker
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listWorkerEtcExperienceFile(User $worker, ListQueryParam $param) : PageCollection {
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            $query = WorkerEtcExperienceFile::orderBy($param->order, $param->direction)
                ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                })
                ->where('user_id', $worker->id);
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $collection);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자 이력서를 등록한다.
     * @param User $worker
     * @param WorkerEtcExperienceFileDto $dto
     * @return void
     * @throws HttpException
     */
    public function addWorkerEtcExperience(User $worker, WorkerEtcExperienceFileDto $dto) : void {
        try {
            if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
                WorkerEtcExperienceFile::create([
                        'user_id' => $worker->id,
                        'write_user_id' => $this->user->id
                    ] + $dto->toArray());
            }
            else throw HttpException::getInstance(403);
        } catch (Exception $e) {
            $dto->rollback();
            throw $e;
        }
    }

    /**
     * 근로자의 이력서를 변경한다.
     * @param WorkerEtcExperienceFile $file
     * @param WorkerEtcExperienceFileDto $dto
     * @return void
     * @throws HttpException
     */
    public function updateWorkerEtcExperienceFile(
        WorkerEtcExperienceFile $file, WorkerEtcExperienceFileDto $dto) : void {
        try {
            $worker = User::findMe($file->user_id);
            if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
                if($file->file_path && $dto->getFilePath())
                    Storage::disk('local')->delete($file->file_path);
                $file->fill($dto->toArray());
                $file->save();
            }
            else throw HttpException::getInstance(403);
        } catch (Exception $e) {
            $dto->rollback();;
            throw $e;
        }
    }

    /**
     * 근로자의 이력서를 삭제한다.
     * @param WorkerEtcExperienceFile $file
     * @return void
     * @throws HttpException
     */
    public function deleteWorkerEtcExperienceFile(WorkerEtcExperienceFile $file) : void {
        $worker = User::findMe($file->user_id);
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) $file->delete();
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 이력서 파일 내용을 출력한다.
     * @param WorkerEtcExperienceFile $file
     * @return mixed
     */
    public function showWorkerEtcExperienceFile(WorkerEtcExperienceFile $file) : mixed {
        return show_file('local', $file->file_path, $file->file_name);
    }

    /**
     * 지정 사용자의 학력정보 목록을 리턴한다.
     * @param User $worker
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listWorkerEducation(User $worker, ListQueryParam $param) : PageCollection {
        if ($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            $query = WorkerEducation::orderBy($param->order, $param->direction)
                ->where('user_id', $worker->id)
                ->when($param->field && $param->keyword, function(Builder $query) use($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                });
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $result = $query->skip($param->start_rec_no)->take($param->page_per_items)->get();
            return new PageCollection($total, $total_page, $result);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 지정 학력정보를 리턴한다.
     * @param WorkerEducation $education
     * @return WorkerEducation
     * @throws HttpException
     */
    public function getWorkerEducation(WorkerEducation $education) : WorkerEducation {
        $worker = User::findMe($education->user_id);
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) return $education;
        else throw HttpException::getInstance(403);
    }

    /**
     * 지정 근로자의 학력정보를 등록한다.
     * @param User $worker
     * @param WorkerEducationDto $dto
     * @return void
     * @throws HttpException
     */
    public function addWorkerEducation(User $worker, WorkerEducationDto $dto) : void {
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            WorkerEducation::create([
                'user_id' => $worker->id,
                'write_user_id' => $this->user->id
            ] + $dto->toArray());
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 지정 학력정보를 갱신한다.
     * @param WorkerEducationDto $dto
     * @param WorkerEducation $education
     * @return void
     * @throws HttpException
     */
    public function updateWorkerEducation(WorkerEducationDto $dto, WorkerEducation $education) : void {
        $worker = User::findMe($education->user_id);
        if($dto->getFilePath() && $education->file_path) Storage::disk('local')->delete($education->file_path);
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) {
            $education->fill($dto->toArray());
            $education->save();
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 지정 학력정보를 삭제한다.
     * @param WorkerEducation $education
     * @return void
     * @throws HttpException
     */
    public function deleteWorkerEducation(WorkerEducation $education) : void {
        $worker = User::findMe($education->user_id);
        if($this->user->id == $worker->id || $this->isManagedWorker($worker)) $education->delete();
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자의 학력관련 파일 내용을 출력한다.
     * @param WorkerEducation $education
     * @return mixed
     */
    public function showWorkerEtcEducationFile(WorkerEducation $education) : mixed {
        return show_file('local', $education->file_path, $education->origin_name);
    }
}
