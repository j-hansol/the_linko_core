<?php

namespace App\Services\V1;

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
use App\DTOs\V1\WorkerProfileDto;
use App\Events\ConsultingMessageCreated;
use App\Events\VisaIssueTaskAssigned;
use App\Http\QueryParams\ListQueryParam;
use App\Lib\EducationDegree;
use App\Lib\JobType;
use App\Lib\MaritalStatus;
use App\Lib\MemberType;
use App\Lib\PageCollection;
use App\Lib\PassportType;
use App\Lib\VisaApplicationStatus;
use App\Lib\VisitPurpose;
use App\Models\ConsultingMessage;
use App\Models\RequestConsultingPermission;
use App\Models\User;
use App\Models\VisaApplication;
use App\Models\VisaApplicationIssuedInfo;
use App\Models\VisaAssistant;
use App\Models\VisaContact;
use App\Models\VisaCost;
use App\Models\VisaDocument;
use App\Models\VisaEducation;
use App\Models\VisaEmployment;
use App\Models\VisaFamily;
use App\Models\VisaInvitor;
use App\Models\VisaPassport;
use App\Models\VisaPhoto;
use App\Models\VisaProfile;
use App\Models\VisaVisitDetail;
use App\Rules\RequiredOrNull;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Telegram\Bot\Laravel\Facades\Telegram;

class VisaApplicationService {
    protected ?User $user;
    protected ?User $manager;
    protected ?User $manager_operator;
    private array $invalid_fields = [];

    /**
     * 현재 로그인 사용자 및 관리기관, 실무ㅜ자 정보를 초기화한다.
     */
    function __construct() {
        $this->user = current_user();
        $this->manager = $this->user?->getAffiliationManager();
        $this->manager_operator = $this->manager?->id == $this->user?->id ? null : $this->user;
    }

    /**
     * 현재 로그인 사용자의 소속 기관 계정정보를 리턴한다.
     * @return User|null
     */
    public function getManager() :?User {return $this->manager;}

    /**
     * 검토 및 수정이 필요한 문제 항목을 등록하거나 리턴한다.
     * @param string $label
     * @param mixed|null $value
     * @return mixed
     */
    private function _setInvalidField(string $label, mixed $value = null) : mixed {
        $t_labels = explode('.', $label);
        $len = count($t_labels);
        if($len > 0) {
            $temp = &$this->invalid_fields;
            foreach($t_labels as $index => $l) {
                if(!is_array($temp) && $index != ($len - 1)) {
                    $temp = [];
                }
                if(array_key_exists($l, $temp)) $temp = &$temp[$l];
                else {
                    if($value) {
                        if($index != ($len - 1)) {
                            $temp[$l] = [];
                            $temp = &$temp[$l];
                        }
                        else $temp[$l] = $value;
                    }
                    else return null;
                }
            }

            if(!$value) $temp = $value;
            return $temp;
        }
        return null;
    }

    /**
     * 비자신청 정보 생성과 삭제 가능 여부를 판단한다.
     * @param VisaApplication $visa
     * @return bool
     */
    private function isCriticalAccessAbleVisaApplication(VisaApplication $visa) : bool {
        return (($visa->user_id == $this->user->id ||
            $this->manager?->isInManagementUser(User::findMe($visa->user_id))) &&
            $visa->status != VisaApplicationStatus::STATUS_ISSUE_COMPLETE->value);
    }

    /**
     * 비자정보의 일반적인 접근 가능 여부를 판단한다.
     * @param VisaApplication $visa
     * @return bool
     */
    private function isAccessAbleVisaApplication(VisaApplication $visa) : bool {
        return ($this->isCriticalAccessAbleVisaApplication($visa) || $visa->consulting_user_id == $this->user->id ||
            $visa->attorney_user_id == $this->user->id);
    }

    /**
     * 서비스 프로바이더를 통해 인스턴스를 가져온다.
     * @return VisaApplicationService
     * @throws Exception
     */
    public static function getInstance() : VisaApplicationService {
        $instance = app(static::class);
        if(!$instance) throw new Exception('service not constructed');
        return $instance;
    }

    /**
     * 로그인 사용자 또는 지정 사용자의 비자신청 목록을 리턴한다.
     * @param ListQueryParam $param
     * @param User|null $user
     * @param array $filters
     * @return PageCollection
     * @throws HttpException
     */
    public function listVisaApplication(ListQueryParam $param, ?User $user = null, array $filters = []) : PageCollection {
        if(!$user) $user = $this->user;
        elseif(!$this->manager->isInManagementUser($user)) throw HttpException::getInstance(403);

        $query = VisaApplication::orderBy($param->order, $param->direction)
            ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                $query->where($param->field, $param->operator, $param->keyword);
            })
            ->where('user_id', $user->id)
            ->when($filters, function(Builder $query) use($filters) {
                foreach($filters as $filter) {
                    if($filter['op'] == 'in') $query->whereIn($filter['field'], $filter['value']);
                    else $query->where($filter['field'], $filter['op'], $filter['value']);
                }
            });
        $total = $query->count();
        $total_page = ceil($total / $param->page_per_items);
        $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
            ->get();
        return new PageCollection($total, $total_page, $collection);
    }

    /**
     * 행정사에게 컨설팅 가능한 비자정보 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listConsultAbleVisa(ListQueryParam $param) : PageCollection {
        if($this->user->isOwnType(MemberType::TYPE_ATTORNEY)) {
            $query = VisaApplication::orderBy($param->order, $param->direction)
                ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                })
                ->where('status', '<', VisaApplicationStatus::STATUS_START_PREVIEW->value)
                ->whereNull('consulting_user_id');
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $collection = $query->skip(($param->page - 1) * $param->page_per_items)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $collection);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 행정사가 컨설팅 중인 비자정보 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listConsultingVisa(ListQueryParam $param) : PageCollection {
        if($this->user->isOwnType(MemberType::TYPE_ATTORNEY)) {
            $query = VisaApplication::orderBy($param->order, $param->direction)
                ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                })
                ->where('status', '<=', VisaApplicationStatus::STATUS_ISSUE_AVAILABLE->value)
                ->where('consulting_user_id', $this->user->id );
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $collection = $query->skip(($param->page - 1) * $param->page_per_items)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $collection);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 행정사에게 컨설팅이 완료된 비자정보 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listConsultedVisa(ListQueryParam $param) : PageCollection {
        if($this->user->isOwnType(MemberType::TYPE_ATTORNEY)) {
            $query = VisaApplication::orderBy($param->order, $param->direction)
                ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                })
                ->where('status', '>=', VisaApplicationStatus::STATUS_ISSUE_APPLICATION->value)
                ->where('consulting_user_id', $this->user->id );
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $collection = $query->skip(($param->page - 1) * $param->page_per_items)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $collection);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자 본인 또는 관리기관에서 근로자 비자 신청서를 기록한다.
     * @param RequestVisaApplicationDto $dto
     * @param User|null $user
     * @return VisaApplication|null
     * @throws HttpException
     */
    public function requestVisaApplication(RequestVisaApplicationDto $dto, User $user = null) : ?VisaApplication {
        if(!$user) $user = $this->user;
        elseif(!$this->manager->isInManagementUser($user)) throw HttpException::getInstance(403);

        $visa = VisaApplication::create([
            'user_id' => $user->id,
            'order_stay_period' => $dto->getOrderStayPeriod(),
            'order_stay_status' => $dto->getOrderStayStatus()
        ]);
        VisaProfile::createFrom($visa, $user);
        return $visa;
    }

    /**
     * 비자정보를 열람 가능한 사용자에게 비자정보를 리턴한다.
     * @param VisaApplication $visa
     * @return VisaApplication
     * @throws HttpException
     */
    public function getVisaApplication(VisaApplication $visa) : VisaApplication {
        if($this->isAccessAbleVisaApplication($visa)) return $visa;
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자 본인 또는 관료자를 관리하는 기관에서 근로자의 비자신청 정보를 변경한다.
     * @param VisaApplication $visa
     * @param RequestVisaApplicationDto $dto
     * @return void
     * @throws HttpException
     */
    public function updateVisaApplication(RequestVisaApplicationDto $dto, VisaApplication $visa) : void {
        if($this->isCriticalAccessAbleVisaApplication($visa) && $visa->isUpdateAble()) {
            $visa->order_stay_period = $dto->getOrderStayPeriod();
            $visa->order_stay_status = $dto->getOrderStayStatus();
            $visa->save();
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 비자신청정보의 상태를 변경한다.
     * @param VisaApplicationStatus $status
     * @param VisaApplication $visa
     * @param VisaApplicationIssuedInfoDto|null $dto
     * @return void
     * @throws HttpException|Exception
     */
    public function updateVisaApplicationStatus(
        VisaApplicationStatus $status,
        VisaApplication $visa,
        ?VisaApplicationIssuedInfoDto $dto) : void {
        if($this->isAccessAbleVisaApplication($visa)) {
            $current_status = VisaApplicationStatus::tryFrom($visa->status);
            if($visa->status == VisaApplicationStatus::STATUS_ISSUE_COMPLETE->value)
                throw HttpErrorsException::getInstance([__('errors.visa.invalid_workflow')], 406);
            if($this->user->id == $visa->user_id || $this->manager->isInManagementUser(User::findMe($visa->user_id))) {
                if(!VisaApplication::isAbleStatus($status, MemberType::TYPE_FOREIGN_PERSON))
                    throw HttpErrorsException::getInstance([__('errors.visa.invalid_workflow')], 406);
            }
            else if(!$visa->isAbleStatusForAttorney($this->user, $status))
                throw HttpErrorsException::getInstance([__('errors.visa.no_attorney_workflow')], 406);
            else throw HttpException::getInstance(403);
            if(!$current_status?->isValidWorkflow($status))
                throw HttpErrorsException::getInstance([__('errors.visa.invalid_workflow')], 406);
            if($current_status == VisaApplicationStatus::STATUS_ISSUE_COMPLETE && !$dto)
                throw HttpErrorsException::getInstance([__('errors.visa.invalid_workflow')], 406);

            DB::beginTransaction();
            try {
                $visa->status = $status->value;
                $visa->save();
                if($dto) {
                    VisaApplicationIssuedInfo::create([
                        'visa_application_id' => $visa->id,
                        'user_id' => $visa->user_id,
                        'attorney_user_id' => $this->user->id] + $dto->toArray());
                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 현재의 비자 발급 상태 정보를 바탕으로 다음 설정 가능한 상태정보(번호) 목록을 배열로 리턴한다.
     * @param VisaApplication $visa
     * @return array
     * @throws HttpException
     */
    public function getAvailableVisaStatus(VisaApplication $visa) : array {
        $current_status = VisaApplicationStatus::tryFrom($visa->status);
        if($this->user) {
            $worker = User::findMe($visa->user_id);
            $consulting_user = $visa->consulting_user_id ? User::findMe($visa->consulting_user_id) : null;
            $attorney_user = $visa->attorney_user_id ? User::findMe($visa->attorney_user_id) : null;
            $next_status = VisaApplicationStatus::getWorkflow($current_status);
            if($worker->id == $this->user->id || $this->manager?->id == $worker->management_org_id) {
                $t = [];
                foreach($next_status as $status)
                    if(VisaApplication::isAbleStatus($status, MemberType::TYPE_FOREIGN_PERSON))
                        $t[] = $status->value;
                return $t;
            }
            elseif($consulting_user?->id == $this->user->id || $attorney_user?->id == $this->user->id) {
                $t = [];
                foreach($next_status as $status)
                    if(VisaApplication::isAbleStatus($status, MemberType::TYPE_ATTORNEY))
                        $t[] = $status->value;
                return $t;
            }
            else return [];
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 비자신청 정보를 삭제한다.
     * @param VisaApplication $visa
     * @return void
     * @throws HttpException
     */
    public function deleteVisaApplication(VisaApplication $visa) : void {
        if($this->isCriticalAccessAbleVisaApplication($visa) && $visa->isDeleteAble()) {
            $visa->delete();
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자 본인 또는 관리 기관에서 비자 신청에 필요한 프로필 정보를 변경한다.
     * @param WorkerProfileDto $dto
     * @param VisaApplication $visa
     * @return void
     * @throws HttpException
     */
    public function updateProfile(WorkerProfileDto $dto, VisaApplication $visa) : void {
        if($this->isCriticalAccessAbleVisaApplication($visa) && $visa->isUpdateAble()) {
            $profile = VisaProfile::findByVisa($visa);
            if($profile) {
                $profile->fill($dto->toArray());
                $profile->save();
            }
            else throw HttpException::getInstance(404);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 비자신청 정보에 사진을 등록한다.
     * @param UploadedFile $file
     * @param VisaApplication $visa
     * @return void
     * @throws HttpException
     */
    public function setPhoto(UploadedFile $file, VisaApplication $visa) : void {
        if($this->isCriticalAccessAbleVisaApplication($visa) && $visa->isUpdateAble()) {
            $photo = VisaPhoto::findByVisa($visa);
            if(!$photo) VisaPhoto::createFrom($visa, $file);
            else $photo->updatePhoto($file);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 사진을 출력한다.
     * @param VisaApplication $visa
     * @return mixed
     * @throws HttpException
     */
    public function showPhoto(VisaApplication $visa) : mixed {
        $photo = VisaPhoto::findByVisa($visa);
        if(!$photo) throw HttpException::getInstance(404);
        else return show_file('local', $photo->file_path);
    }

    /**
     * 비자신청 정보에 여권정보를 설정한다.
     * @param PassportDto $dto
     * @param VisaApplication $visa
     * @return void
     */
    public function setPassport(PassportDto $dto, VisaApplication $visa) : void {
        if($this->isCriticalAccessAbleVisaApplication($visa)) {
            $passport = VisaPassport::findByVisa($visa);
            if($passport) {
                $passport->fill($dto->toArray());
                $passport->save();
            }
            else {
                VisaPassport::create([
                    'visa_application_id' => $visa->id,
                    'user_id' => $visa->user_id,
                ] + $dto->toArray());
            }
        }
        else HttpException::getInstance(403);
    }

    /**
     * 여권 사본을 출력한다.
     * @param VisaApplication $visa
     * @return mixed
     * @throws HttpException
     */
    public function showPassportFile(VisaApplication $visa) : mixed {
        $passport = VisaPassport::findByVisa($visa);
        if($passport?->file_path) return show_file('local', $passport?->file_path);
        else throw HttpException::getInstance(404);
    }

    /**
     * 비자신청 정보에 연락처 정보를 설정한다.
     * @param ContactDto $dto
     * @param VisaApplication $visa
     * @return void
     * @throws HttpException
     */
    public function setContact(ContactDto $dto, VisaApplication $visa) : void {
        if($this->isCriticalAccessAbleVisaApplication($visa)) {
            $contact = VisaContact::findByVisa($visa);
            if($contact) {
                $contact->fill($dto->toArray());
                $contact->save();
            }
            else {
                VisaContact::create([
                    'visa_application_id' => $visa->id,
                    'user_id' => $visa->user_id,
                ] + $dto->toArray());
            }
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 비자신청 정보에 가족사항을 설정한다.
     * @param FamilyDetailDto $dto
     * @param VisaApplication $visa
     * @return void
     * @throws HttpException
     */
    public function setFamilyDetail(FamilyDetailDto $dto, VisaApplication $visa) : void {
        if($this->isCriticalAccessAbleVisaApplication($visa)) {
            $detail = VisaFamily::findByVisa($visa);
            if($detail) {
                $detail->fill($dto->toArray());
                $detail->save();
            }
            else {
                VisaFamily::create([
                    'visa_application_id' => $visa->id,
                    'user_id' => $visa->user_id,
                ] + $dto->toArray());
            }
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 비자신청 정보에 학력정보를 설정한다.
     * @param VisaEducationDto $dto
     * @param VisaApplication $visa
     * @return void
     * @throws HttpException
     */
    public function setEducation(VisaEducationDto $dto, VisaApplication $visa) : void {
        if($this->isCriticalAccessAbleVisaApplication($visa)) {
            $education = VisaEducation::findByVisa($visa);
            if($education) {
                $education->fill($dto->toArray());
                $education->save();
            }
            else {
                VisaEducation::create([
                    'visa_application_id' => $visa->id,
                    'user_id' => $visa->user_id,
                ] + $dto->toArray());
            }
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 비자신청 정보에 직업상태 정보를 설정한다.
     * @param VisaEmploymentDto $dto
     * @param VisaApplication $visa
     * @return void
     * @throws HttpException
     */
    public function setEmployment(VisaEmploymentDto $dto, VisaApplication $visa) : void {
        if($this->isCriticalAccessAbleVisaApplication($visa)) {
            $employ = VisaEmployment::findByVisa($visa);
            if($employ) {
                $employ->fill($dto->toArray());
                $employ->save();
            }
            else {
                VisaEmployment::create([
                    'visa_application_id' => $visa->id,
                    'user_id' => $visa->user_id,
                ] + $dto->toArray());
            }
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 비자신청 정보에 방문정보를 설정한다.
     * @param VisitDetailDto $dto
     * @param VisaApplication $visa
     * @return void
     * @throws HttpException
     */
    public function setVisitDetail(VisitDetailDto $dto, VisaApplication $visa) : void {
        if($this->isCriticalAccessAbleVisaApplication($visa)) {
            $detail = VisaVisitDetail::findByVisa($visa);
            if($detail) {
                $detail->fill($dto->toArray());
                $detail->save();
            }
            else {
                VisaVisitDetail::create([
                    'visa_application_id' => $visa->id,
                    'user_id' => $visa->user_id,
                ] + $dto->toArray());
            }
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 비자 방문정보의 한국방문 내역, 다른 나라 방문 내역, 국내 거주 가종, 동반 입국 가족 정보를 설정한다.
     * @param IdsDto $dto
     * @param VisaApplication $visa
     * @param string $field_name
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function setVisitDetailFieldIds(IdsDto $dto, VisaApplication $visa, string $field_name) : void {
        if($this->isCriticalAccessAbleVisaApplication($visa)) {
            $detail = VisaVisitDetail::findByVisa($visa);
            if($detail) {
                $detail->setAttribute($field_name, json_encode($dto->getIds()));
                $detail->save();
            }
            else throw HttpErrorsException::getInstance([__('errors.visa.not_found')], 406);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 비자신청 정보에 초청인 정보를 설정한다.
     * @param InvitorDto $dto
     * @param VisaApplication $visa
     * @return void
     * @throws HttpException
     */
    public function setInvitor(InvitorDto $dto, VisaApplication $visa) : void {
        if($this->isCriticalAccessAbleVisaApplication($visa)) {
            $invitor = VisaInvitor::findByVisa($visa);
            if($invitor) {
                $invitor->fill($dto->toArray());
                $invitor->save();
            }
            else {
                VisaInvitor::create([
                    'visa_application_id' => $visa->id,
                    'user_id' => $visa->user_id,
                ] + $dto->toArray());
            }
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 비자신청 정보에 방문경비 정보를 설정한다.
     * @param FundingDetailDto $dto
     * @param VisaApplication $visa
     * @return void
     * @throws HttpException
     */
    public function setFundingDetail(FundingDetailDto $dto, VisaApplication $visa) : void {
        if($this->isCriticalAccessAbleVisaApplication($visa)) {
            $detail = VisaCost::findByVisa($visa);
            if($detail) {
                $detail->fill($dto->toArray());
                $detail->save();
            }
            else {
                VisaCost::create([
                    'visa_application_id' => $visa->id,
                    'user_id' => $visa->user_id,
                ] + $dto->toArray());
            }
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 비자신청 정보에 서류작성 도움 정보를 설정한다.
     * @param AssistanceDto $dto
     * @param VisaApplication $visa
     * @return void
     */
    public function setAssistance(AssistanceDto $dto, VisaApplication $visa) : void {
        if($this->isCriticalAccessAbleVisaApplication($visa)) {
            $assistant = VisaAssistant::findByVisa($visa);
            if($assistant) {
                $assistant->fill($dto->toArray());
                $assistant->save();
            }
            else {
                VisaAssistant::create([
                    'visa_application_id' => $visa->id,
                    'user_id' => $visa->user_id,
                ] + $dto->toArray());
            }
        }
        else HttpException::getInstance(403);
    }

    /**
     * 비자신청 문서를 등록한다.
     * @param VisaDocumentDto $dto
     * @param VisaApplication $visa
     * @return void
     * @throws HttpException
     */
    public function addDocument(VisaDocumentDto $dto, VisaApplication $visa) : void {
        if($this->isCriticalAccessAbleVisaApplication($visa)) {
            VisaDocument::create([
                'visa_application_id' => $visa->id,
                'user_id' => $visa->user_id,
            ] + $dto->toArray());
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 비자신청 문서정보를 변경한다.
     * @param VisaDocumentDto $dto
     * @param VisaDocument $document
     * @return void
     * @throws HttpException
     */
    public function updateDocument(VisaDocumentDto $dto, VisaDocument $document) : void {
        $visa = VisaApplication::findMe($document->visa_application_id);
        if($this->isCriticalAccessAbleVisaApplication($visa)) {
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
     * @param VisaDocument $document
     * @return void
     * @throws HttpException
     */
    public function deleteDocument(VisaDocument $document) : void {
        $visa = VisaApplication::findMe($document->visa_application_id);
        if($this->isCriticalAccessAbleVisaApplication($visa)) $document->delete();
        else throw HttpException::getInstance(403);
    }

    /**
     * 문서 내용을 출력한다.
     * @param VisaDocument $document
     * @return mixed
     */
    public function showDocumentFile(VisaDocument $document) : mixed {
        return show_file('local', $document->file_path);
    }

    /**
     * 비자신청관련 메시지 전송 가능 여부를 판단한다.
     * @param VisaApplication $visa
     * @return bool
     */
    private function isMessageSendable(VisaApplication $visa) : bool {
        $worker = User::findMe($visa->user_id);
        if($visa->status == VisaApplicationStatus::STATUS_REGISTERING->value ||
            $visa->isConsulting() || $visa->isInIssueProcess()) {
            if($this->user->isOwnType(MemberType::TYPE_FOREIGN_MANAGER) &&
                $worker->management_org_id != $this->user->id) return false;
            if($this->user->isOwnType(MemberType::TYPE_FOREIGN_MANAGER_OPERATOR) &&
                $worker->management_org_id != $this->user->management_org_id) return false;
                if($this->user->isOwnType(MemberType::TYPE_FOREIGN_PERSON)
                && $this->user->id != $visa->user_id) return false;
            if($this->user->isOwnType(MemberType::TYPE_ATTORNEY) &&
                !($visa->consulting_user_id == $this->user->id || $visa->attorney_user_id == $this->user->id))
                return false;
            return true;
        }
        else return false;
    }

    /**
     * 비자신청관련 메시지를 전송한다.
     * @param VisaMessageDto $dto
     * @param VisaApplication $visa
     * @return void
     * @throws Exception
     */
    public function sendMessage(VisaMessageDto $dto, VisaApplication $visa) : void {
        $worker = User::findMe($visa->user_id);
        if($this->isMessageSendable($visa)) {
            $sender = $this->user;
            if($this->user->id != $visa->user_id &&
                $this->user->getAffiliationManager()?->id == $worker->management_org_id) $sender = $worker;
            DB::beginTransaction();
            try {
                $message = ConsultingMessage::create([
                    'visa_application_id' => $visa->id,
                    'user_id' => $sender->id,
                ] + $dto->toArray());
                ConsultingMessageCreated::dispatch($message);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 해당 비자에 대해 컨설팅 행정사를 지정한다.
     * @param User $attorney
     * @param VisaApplication $visa
     * @return void
     * @throws HttpErrorsException
     */
    public function setConsultingAttorney(User $attorney, VisaApplication $visa) : void {
        if(!RequestConsultingPermission::setConfirmed($visa, $attorney))
            throw HttpErrorsException::getInstance([__('errors.visa.cannot_consulting_attorney')], 406);
    }

    /**
     * 다수의 비자신청에 대한 컨설팅 행정사를 지정한다.
     * @param array $ids
     * @param User $attorney
     * @return void
     * @throws HttpErrorsException
     */
    public function setConsultingAttorneyMultiple(array $ids, User $attorney) : void {
        if(!RequestConsultingPermission::setConfirmedMultiple($ids, $attorney))
            throw HttpErrorsException::getInstance([__('errors.visa.cannot_consulting_attorney')], 406);
    }

    /**
     * 컨설팅 중인 비자신청정보 목록을 리턴한다. (관리자 전용)
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listConsultingVisaForOperator(ListQueryParam $param) : PageCollection {
        return $this->listVisaApplication($param, null, [
            ['field' => 'status', 'op' => '>=', 'value' => VisaApplicationStatus::STATUS_ISSUE_AVAILABLE->value]
        ]);
    }

    /**
     * 컨설팅이 완료된 비자신청정보 목록을 리턴한다. (관리자 전용)
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listConsultedVisaForOperator(ListQueryParam $param) : PageCollection {
        return $this->listVisaApplication($param, null, [
            ['field' => 'status', 'op' => '>=', 'value' => VisaApplicationStatus::STATUS_ISSUE_APPLICATION->value]
        ]);
    }

    /**
     * 행정사가 해당 비자신청정보에 대해 컨설팅 권한을 요청한다.
     * @param VisaApplication $visa
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function requestConsultingPermission(VisaApplication $visa) : void {
        if($this->user->isOwnType(MemberType::TYPE_ATTORNEY)) {
            if(RequestConsultingPermission::isAssigned($visa) ||
                RequestConsultingPermission::isRequested($visa, $this->user))
                throw HttpErrorsException::getInstance([__('errors.visa.cannot_consulting_attorney')], 406);
            RequestConsultingPermission::createRequestPermission($visa, $this->user);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 지정 비자신청정보의 컨설팅 권한 요청정보 목록을 리턴한다.
     * @param VisaApplication $visa
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listRequestPermission(VisaApplication $visa, ListQueryParam $param) : PageCollection {
        $query = RequestConsultingPermission::orderBy($param->order, $param->direction)
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
     * 지정 비자밝듭 신청업무를 행정사에게 배정한다.
     * @param VisaApplication $visa
     * @param User $attorney
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function assignIssueTask(VisaApplication $visa, User $attorney) : void {
        if($visa->isAssignAble()) {
            if($this->user->isOwnType(MemberType::TYPE_OPERATOR) &&
                $attorney->isOwnType(MemberType::TYPE_ATTORNEY)) {
                $visa->attorney_user_id = $attorney->id;
                $visa->save();
                VisaIssueTaskAssigned::dispatch($visa);
            }
            else throw HttpException::getInstance(403);
        }
        else throw HttpErrorsException::getInstance([__('errors.visa.cannot_assign_issue_attorney')], 406);
    }

    /**
     * 전달된 비자발급 신청정보 목록에 발급업무 진행 행정사를 배정한다.
     * @param array $ids
     * @param User $attorney
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function assignIssueTaskMultiple(array $ids, User $attorney) : void {
        if($this->user->isOwnType(MemberType::TYPE_OPERATOR) &&
            $attorney->isOwnType(MemberType::TYPE_ATTORNEY)) {
            $visas = VisaApplication::find($ids);
            if($visas->isEmpty()) throw HttpException::getInstance(404);
            $saved_list = new Collection();
            foreach($visas as $visa) {
                if(!$visa->isAssignAble()) continue;
                $visa->attorney_user_id = $attorney->id;
                $visa->save();
                $saved_list->add($visa);
            }
            if($saved_list->isNotEmpty()) VisaIssueTaskAssigned::dispatch($saved_list);
            else throw HttpErrorsException::getInstance([__('errors.visa.cannot_assign_issue_attorney')], 406);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 비자 신청정보를 JSON 형식으로 받아 등록한다.
     * @param VisaApplicationJsonDto $dto
     * @param User $worker
     * @return array
     * @throws HttpErrorsException|HttpException
     */
    public function requestVisaApplicationFromJson(VisaApplicationJsonDto $dto, User $worker) : array {
        if(!$worker->isOwnType(MemberType::TYPE_FOREIGN_PERSON))
            throw HttpErrorsException::getInstance([__('errors.user.only_foreign_person')], 406);
        if($worker->id != $this->user->id && !$this->manager->isInManagementUser($worker)) throw HttpException::getInstance(403);

        $infos = $dto->toArray($worker);
        $post_save_required = false;
        if($this->_isValidVisaApplicationMaster($infos)) {
            DB::beginTransaction();
            try {
                $visa = VisaApplication::create([
                    'user_id' => $worker->id,
                    'order_stay_period' => $dto->getOrderStayPeriod(),
                    'order_stay_status' => $dto->getOrderStayStatus()
                ]);

                if(!$this->_isAllEmptyArray($infos['profile']) &&
                    $this->_isValidVisaProfile($infos['profile'])) {
                    VisaProfile::create([
                        'visa_application_id' => $visa->id,
                        'user_id' => $worker->id,
                    ] + $infos['profile']);
                }
                if(!$this->_isAllEmptyArray($infos['passport']) &&
                    $this->_isValidVisaPassport($infos['passport'])) {
                    VisaPassport::create([
                            'visa_application_id' => $visa->id,
                            'user_id' => $worker->id,
                        ] + $infos['passport']);
                }
                if(!$this->_isAllEmptyArray($infos['contact']) &&
                    $this->_isValidVisaContact($infos['contact'])) {
                    VisaContact::create([
                            'visa_application_id' => $visa->id,
                            'user_id' => $worker->id,
                        ] + $infos['contact']);
                }
                if(!$this->_isAllEmptyArray($infos['families']) &&
                    $this->_isValidVisaFamilyDetail($infos['families'])) {
                        VisaFamily::create([
                            'visa_application_id' => $visa->id,
                            'user_id' => $worker->id,
                        ] + $infos['families']);
                }
                if($this->_isValidVisaEducation($infos['education'])) {
                    VisaEducation::create([
                            'visa_application_id' => $visa->id,
                            'user_id' => $worker->id,
                        ] + $infos['education']);
                }
                if(!$this->_isAllEmptyArray($infos['employment']) &&
                    $this->_isValidVisaEmployment($infos['employment'])) {
                    VisaEmployment::create([
                            'visa_application_id' => $visa->id,
                            'user_id' => $worker->id,
                        ] + $infos['employment']);
                }
                if(!$this->_isAllEmptyArray($infos['visit_detail']) &&
                    $this->_isValidVisaVisitDetail($infos['visit_detail'])) {
                    VisaVisitDetail::create([
                            'visa_application_id' => $visa->id,
                            'user_id' => $worker->id,
                        ] + $infos['visit_detail']);
                }
                if($this->_isValidVisaFundingDetail($infos['cost'])) {
                    if(!$this->_isAllEmptyArray($infos['cost'])) {
                        VisaCost::create([
                                'visa_application_id' => $visa->id,
                                'user_id' => $worker->id,
                            ] + $infos['cost']);
                    }
                }
                if($this->_isValidVisaInvitor($infos['invitor'])) {
                    if(!$this->_isAllEmptyArray($infos['invitor'])) {
                        VisaInvitor::create([
                                'visa_application_id' => $visa->id,
                                'user_id' => $worker->id,
                            ] + $infos['invitor']);
                    }
                }
                if($this->_isValidVisaAssistance($infos['assistant'])) {
                    if(!$this->_isAllEmptyArray($infos['assistant'])) {
                        VisaAssistant::create([
                                'visa_application_id' => $visa->id,
                                'user_id' => $worker->id,
                            ] + $infos['assistant']);
                    }
                }
                foreach($dto->getCheckItems() as $label) {
                    $this->_setInvalidField($label, true);
                }
                if(!empty($this->invalid_fields)) {
                    $invalid_fields = json_encode($this->invalid_fields);
                    $visa->invalid_fields = $invalid_fields;
                    $visa->save();
                    Log::info('Visa Application Info Created', $visa->toArray());;
                    telegram_message('비자신청 정보가 등록되었습니다.');
                }
                DB::commit();

                return [
                    'code' => !empty($this->invalid_fields) ? 201 : 200,
                    'visa' => $visa
                ];
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
        else throw HttpErrorsException::getInstance([__('errors.visa.invalid_application_info')], 400);
    }

    /**
     * 비자 마스터 정보의 유효성 여부를 판단한다.
     * @param array $info
     * @return bool
     */
    private function _isValidVisaApplicationMaster(array $info) : bool {
        return Validator::make($info, [
            'order_stay_period' => ['required', 'integer', 'in:10,20'],
            'order_stay_status' => ['required']
        ])->passes();
    }

    /**
     * 비자의 프로필정보의 유효성을 판단한다.
     * @param array $profile
     * @return bool
     */
    private function _isValidVisaProfile(array $profile) : bool {
        $this->_checkVisaProfile($profile);
        return true;
    }

    /**
     * 비자의 프로필 정보 검토 항목을 설정한다.
     * @param array $profile
     * @return void
     */
    private function _checkVisaProfile(array $profile) : void {
        $validator = Validator::make($profile, [
            'family_name' => ['required'],
            'given_names' => ['required'],
            'sex' => ['required', 'in:M,F'],
            'birthday' => ['required', 'date', 'date_format:Y-m-d'],
            'nationality_id' => ['required', 'integer', 'exists:countries,id'],
            'birth_country_id' => ['required', 'integer', 'exists:countries,id'],
        ]);

        if($validator->fails()) {
            $fields = array_keys($validator->getMessageBag()->toArray());
            foreach($fields as $field) $this->_setInvalidField('profile.' . $field, true);
        }
    }

    /**
     * 여권정보의 유효성을 판단한다.
     * @param array $passport
     * @return bool
     */
    private function _isValidVisaPassport(array $passport) : bool {
        $validator = Validator::make($passport, [
            'passport_country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'issue_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'expire_date' => ['nullable', 'date', 'date_format:Y-m-d'],
        ]);
        $result = $validator->passes();
        $this->_checkVisaPassport($passport);
        return $result;
    }

    /**
     * 여권정보 검토 항목을 등록한다.
     * @param array $passport
     * @return void
     */
    private function _checkVisaPassport(array $passport) : void {
        $validator = Validator::make($passport, [
            'passport_type' => ['required', new Enum(PassportType::class)],
            'other_type_detail' => ['required_if:passport_type,' . PassportType::TYPE_OTHER->value],
            'passport_no' => ['required'],
            'passport_country_id' => ['required', 'integer', 'exists:countries,id'],
            'issue_place' => ['required'],
            'issue_date' => ['required', 'date', 'date_format:Y-m-d'],
            'expire_date' => ['required', 'date', 'date_format:Y-m-d'],
            'other_passport_detail' => [(new RequiredOrNull)->required($passport['other_passport'] == 1)],
            'other_passport_type' => [(new RequiredOrNull)->required($passport['other_passport'] == 1)],
            'other_passport_no' => [(new RequiredOrNull)->required($passport['other_passport'] == 1)],
            'other_passport_country_id' => [(new RequiredOrNull)->required($passport['other_passport'] == 1), 'exists:countries,id'],
            'other_passport_expire_date' => ['required', 'date', 'date_format:Y-m-d']
        ]);

        if($validator->fails()) {
            $fields = array_keys($validator->getMessageBag()->toArray());
            foreach($fields as $field) $this->_setInvalidField('passport.' . $field, true);
        }
    }

    /**
     * 연락처 정보의 유효성을 판단한다.
     * @param array $contact
     * @return bool
     */
    private function _isValidVisaContact(array $contact) : bool {
        $validator = Validator::make($contact, [
            'emergency_country_id' => ['nullable', 'integer', 'exists:countries,id'],
        ]);
        $result = $validator->passes();
        $this->_checkVisaContact($contact);
        return $result;
    }

    private function _checkVisaContact(array $contact) : void {
        $validator = Validator::make($contact, [
            'home_address' => ['required'],
            'current_address' => ['nullable'],
            'cell_phone' => ['required'],
            'email' => ['required'],
            'emergency_full_name' => ['required'],
            'emergency_country_id' => ['required', 'integer', 'exists:countries,id'],
            'emergency_telephone' => ['required'],
            'emergency_relationship' => ['required']
        ]);
        if($validator->fails()) {
            $fields = array_keys($validator->getMessageBag()->toArray());
            foreach($fields as $field) $this->_setInvalidField('contact.' . $field, true);
        }
    }

    /**
     * 비자 가족사항 유효성을 판단한다.
     * @param array $detail
     * @return bool
     */
    private function _isValidVisaFamilyDetail(array $detail) : bool {
        $status = $detail['marital_status'];
        $validator = Validator::make($detail, [
            'marital_status' => ['nullable', new Enum(MaritalStatus::class)],
            'spouse_birthday' => ['nullable', 'date', 'date_format:Y-m-d'],
            'spouse_nationality_id' => ['nullable', 'integer', 'exists:countries,id'],
            'number_of_children' => ['nullable', 'integer', 'min:0']
        ]);
        $result = $validator->passes();
        $this->_checkVisaFamilyDetail($detail);
        return $result;
    }

    /**
     * 가족사항 검토 항목을 등록한다.
     * @param array $detail
     * @return void
     */
    private function _checkVisaFamilyDetail(array $detail) : void {
        $status = $detail['marital_status'];
        $validator = Validator::make($detail, [
            'marital_status' => ['required', new Enum(MaritalStatus::class)],
            'spouse_family_name' => [(new RequiredOrNull)->required($status == MaritalStatus::MARRIED->value)],
            'spouse_given_name' => [(new RequiredOrNull)->required($status == MaritalStatus::MARRIED->value)],
            'spouse_birthday' => [(new RequiredOrNull)->required($status == MaritalStatus::MARRIED->value), 'date', 'date_format:Y-m-d'],
            'spouse_nationality_id' => [(new RequiredOrNull)->required($status == MaritalStatus::MARRIED->value), 'integer', 'exists:countries,id'],
            'spouse_residential_address' => [(new RequiredOrNull)->required($status == MaritalStatus::MARRIED->value)],
            'spouse_contact_no' => [(new RequiredOrNull)->required($status == MaritalStatus::MARRIED->value)],
        ]);
        if($validator->fails()) {
            $fields = array_keys($validator->getMessageBag()->toArray());
            foreach($fields as $field) $this->_setInvalidField('family_detail.' . $field, true);
        }
    }

    /**
     * 비자 최종학력정보 유효성을 판단한다.
     * @param array $education
     * @return bool
     */
    private function _isValidVisaEducation(array $education) : bool {
        $degree = $education['highest_degree'];
        $validator = Validator::make($education, [
            'highest_degree' => ['required', new Enum(EducationDegree::class)],
            'other_detail' => [(new RequiredOrNull)->required($degree == EducationDegree::OTHER->value)],
            'school_name' => ['required'],
            'school_location' => ['required']
        ]);
        $result = $validator->passes();
        if(!$result) {
            $fields = array_keys($validator->getMessageBag()->toArray());
            foreach($fields as $field) $this->_setInvalidField('education.' . $field, true);
        }
        return $result;
    }

    /**
     * 비자 직업정보 유효성을 판단한다.
     * @param array $employment
     * @return bool
     */
    private function _isValidVisaEmployment(array $employment) : bool {
        // $job = $employment['job'];
        $validator = Validator::make($employment, [
            'job' => ['required', 'integer', new Enum(JobType::class)],
            'other_detail' => ['nullable'],
            'org_name' => ['nullable'],
            'position_course' => ['nullable'],
            'org_address'  => ['nullable'],
            'org_telephone'  => ['nullable']
        ]);
        $result = $validator->passes();
        if(!$result) {
            $fields = array_keys($validator->getMessageBag()->toArray());
            foreach($fields as $field) $this->_setInvalidField('employment.' . $field, true);
        }
        return $result;
    }

    /**
     * 비자 방문정보 유효성을 판단한다.
     * @param array $detail
     * @return bool
     */
    private function _isValidVisaVisitDetail(array $detail) : bool {
        $purpose = $detail['purpose'];
        $validator = Validator::make($detail, [
            'purpose' => ['required', new Enum(VisitPurpose::class)],
            'other_purpose_detail' => ['nullable'],
            'intended_stay_period' => ['required', 'integer', 'min:1'],
            'intended_entry_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'text_intended_entry_date' => [(new RequiredOrNull())->required(!$detail['intended_entry_date'])],
            'address_in_korea' => ['required'],
            'contact_in_korea' => ['required']
        ]);
        $result = $validator->passes();
        $this->_checkVisaVisitDetail($detail);
        return $result;
    }

    /**
     * 방문정보 검토 항목을 등록한다.
     * @param array $detail
     * @return void
     */
    private function _checkVisaVisitDetail(array $detail) : void {
        $purpose = $detail['purpose'];
        $validator = Validator::make($detail, [
            'purpose' => ['required', new Enum(VisitPurpose::class)],
            'other_purpose_detail' => [(new RequiredOrNull())->required($purpose == VisitPurpose::OTHER->value)],
            'intended_stay_period' => ['required', 'integer', 'min:1'],
            'intended_entry_date' => ['required', 'date', 'date_format:Y-m-d'],
            'address_in_korea' => ['required'],
            'contact_in_korea' => ['required']
        ]);
        if($validator->fails()) {
            $fields = array_keys($validator->getMessageBag()->toArray());
            foreach($fields as $field) $this->_setInvalidField('visit_detail.' . $field, true);
        }
    }

    /**
     * 비자 초청인 정보 유효성을 판단한다.
     * @param array $invitor
     * @return bool
     */
    private function _isValidVisaInvitor(array $invitor) : bool {
        if($this->_isAllEmptyArray($invitor)) return true;

        $validator = Validator::make($invitor, [
            'invitor_birthday' => ['nullable', 'date', 'date_format:Y-m-d'],
        ]);
        $result = $validator->passes();
        $this->_checkVisaInvitor($invitor);
        return $result;
    }

    private function _checkVisaInvitor(array $invitor) : void {
        $validator = Validator::make($invitor, [
            'invitor' => ['required'],
            'invitor_relationship' => ['required'],
            'invitor_birthday' => ['nullable', 'date', 'date_format:Y-m-d'],
            'text_invitor_birthday' => ['nullable'],
            'invitor_registration_no' => ['nullable'],
            'invitor_address' => ['required'],
            'invitor_telephone' => ['nullable'],
            'invitor_cell_phone' => ['nullable']
        ]);
        if($validator->fails()) {
            $fields = array_keys($validator->getMessageBag()->toArray());
            foreach($fields as $field) $this->_setInvalidField('invitor.' . $field, true);
        }
    }

    /**
     * 비자 비용정보의 유효성을 판단한다.
     * @param array $detail
     * @return bool
     */
    private function _isValidVisaFundingDetail(array $detail) : bool {
        if($this->_isAllEmptyArray($detail)) return true;

        $validator = Validator::make($detail, [
            'travel_costs' => ['nullable', 'numeric']
        ]);
        $result = $validator->passes();
        if(!$result) {
            $fields = array_keys($validator->getMessageBag()->toArray());
            foreach($fields as $field) $this->_setInvalidField('funding_detail.' . $field, true);
        }
        return $result;
    }

    /**
     * 비자 서류작성 도움정보 유효성을 판단한다.
     * @param array $assistance
     * @return bool
     */
    private function _isValidVisaAssistance(array $assistance) : bool {
        if($this->_isAllEmptyArray($assistance)) return true;

        $validator = Validator::make($assistance, [
            'consulting_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'assistant_birthday' => ['nullable', 'date', 'date_format:Y-m-d'],
        ]);
        $result = $validator->passes();
        $this->_checkVisaAssistance($assistance);
        return $result;
    }

    private function _checkVisaAssistance(array $assistance) : void {
        $validator = Validator::make($assistance, [
            'consulting_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'assistant_name' => ['required'],
            'assistant_birthday' => ['nullable'],
            'text_assistant_birthday' => [(new RequiredOrNull())->required(!$assistance['assistant_birthday'])],
            'assistant_telephone' => ['required'],
            'assistant_relationship' => ['required']
        ]);
        if($validator->fails()) {
            $fields = array_keys($validator->getMessageBag()->toArray());
            foreach($fields as $field) $this->_setInvalidField('assistant.' . $field, true);
        }
    }

    /**
     * 근로자 계정 생성 가능 여부를 판단한다.
     * @param VisaApplicationJsonDto $dto
     * @return bool
     */
    public function isCreateAbleWorkerAccount(VisaApplicationJsonDto $dto) : bool {
        $infos = $dto->toArray($this->manager);
        return $this->_isValidVisaProfile($infos['profile']) && $this->_isValidVisaContact($infos['contact']);
    }

    /**
     * 배열 전체의 값이 비었는지 여부를 판단한다.
     * @param array $array
     * @return bool
     */
    private function _isAllEmptyArray(array $array) :bool {
        foreach ($array as $value) {
            if(is_numeric($value) && $value != 0)
                return false;
            elseif(is_string($value) && trim($value))
                return false;
            elseif(is_array($value) && !$this->_isAllEmptyArray($value))
                return false;
        }
        return true;
    }

    /**
     * 스켄한 여권정보를 JSON 문자열로 받아 반영한다.
     * @param VisaPassportJsonDto $dto
     * @param VisaApplication $visa
     * @return void
     * @throws HttpErrorsException
     */
    public function updatePassportFromJson(VisaPassportJsonDto $dto, VisaApplication $visa) : void {
        $passport = VisaPassport::findByVisa($visa);
        $info = $dto->toVisaPassportArray();
        if(!$passport) {
            if($this->_isValidVisaPassport($info)) {
                VisaPassport::create([
                        'visa_application_id' => $visa->id,
                        'user_id' => $visa->user_id
                    ] + $info);
            }
            else throw HttpErrorsException::getInstance([__('errors.passport.not_found')], 400);
        }
        else {
            $passport->fill($info);
            $passport->save();
        }
    }
}
