<?php

namespace App\Services\V1;

use App\DTOs\V1\AssignCompanyDto;
use App\DTOs\V1\ContractDto;
use App\DTOs\V1\ContractFileDto;
use App\DTOs\V1\EntryScheduleDto;
use App\DTOs\V1\SubContractDto;
use App\DTOs\V1\UnAssignedCompanyDto;
use App\DTOs\V1\UpdatePlannedWorkerCountDto;
use App\DTOs\V1\WorkerEntryScheduleDto;
use App\DTOs\V1\WorkingCompaniesDto;
use App\Http\QueryParams\ListQueryParam;
use App\Lib\AssignedWorkerStatus;
use App\Lib\ContractPartType;
use App\Lib\ContractStatus;
use App\Lib\ContractType;
use App\Lib\MemberType;
use App\Lib\PageCollection;
use App\Models\AssignedWorker;
use App\Models\Contract;
use App\Models\ContractFile;
use App\Models\EntrySchedule;
use App\Models\EvalInfo;
use App\Models\User;
use App\Models\WorkingCompany;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContractService {
    protected ?User $user;
    protected ?User $order;
    protected ?User $recipient;
    protected ?User $manager;
    protected ?User $manager_operator;
    protected ?Contract $contract;

    /**
     * 현재 로그인 사용자 및 관리기관, 실무ㅜ자 정보를 초기화한다.
     */
    function __construct() {
        $this->user = current_user();
        $this->manager = $this->user->getAffiliationManager();
        $this->manager_operator = $this->manager?->id == $this->user->id ? null : $this->user;
    }

    /**
     * 계약정보 열람 가능 여부를 판단한다.
     * @return bool
     */
    private function _isListAbleOrderedContract(): bool {
        return count(array_intersect([
                MemberType::TYPE_ORDER->value,
                MemberType::TYPE_RECIPIENT->value,
                MemberType::TYPE_MANAGER->value,
                MemberType::TYPE_MANAGER_OPERATOR->value,
                MemberType::TYPE_FOREIGN_MANAGER->value,
                MemberType::TYPE_FOREIGN_MANAGER_OPERATOR->value
            ], $this->user->getTypes()->pluck('type')->toArray())) > 0;
    }

    /**
     * 서비스 프로바이더를 통해 인스턴스를 가져온다.
     * @return ContractService
     * @throws Exception
     */
    public static function getInstance(): ContractService {
        $instance = app(static::class);
        if (!$instance) throw new Exception('service not constructed');
        return $instance;
    }

    /**
     * 발주자가 작성중인 계약정보 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listUndisclosedContract(ListQueryParam $param): PageCollection {
        if ($this->_isListAbleOrderedContract()) {
            $query = Contract::orderBy($param->order, $param->direction)
                ->where('order_user_id', $this->user->id)
                ->where('status', ContractStatus::REGISTERED->value)
                ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                });
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $collection);
        } else throw HttpException::getInstance(403);
    }

    /**
     * 발주 계약 중 공개 이상의 계약정보 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listOrderedContract(ListQueryParam $param): PageCollection {
        if ($this->_isListAbleOrderedContract()) {
            $query = Contract::orderBy($param->order, $param->direction)
                ->when($this->order, function (Builder $query) {
                    $query->where('order_user_id', $this->order->id);
                })
                ->when($this->recipient, function (Builder $query) {
                    $query->where('recipient_user_id', $this->recipient);
                })
                ->when($this->manager, function (Builder $query) {
                    $query->join('contract_managers', 'contracts.id', '=', 'contract_managers.contract_id')
                        ->select('contracts.*')
                        ->where('contract_managers.manager_user_id', $this->manager->id);
                    if ($this->manager->isOwnType(MemberType::TYPE_FOREIGN_MANAGER))
                        $query->where('contract_managers.type', ContractPartType::RECIPIENT_MANAGER->value);
                    elseif ($this->manager->isOwnType(MemberType::TYPE_MANAGER))
                        $query->where('contract_managers.type', ContractPartType::ORDER_MANAGER->value);
                })
                ->where('status', '>=', ContractStatus::PUBLISHED->value)
                ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                });
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $collection);
        } else throw HttpException::getInstance(403);
    }

    /**
     * 신규 계약을 등록한다.
     * @param ContractDto $dto
     * @return void
     * @throws HttpException
     */
    public function addContract(ContractDto $dto): void {
        if ($this->user->isOwnType(MemberType::TYPE_ORDER)) {
            Contract::create([
                    'uuid' => Str::uuid(),
                    'order_user_id' => $this->order->id,
                ] + $dto->toArray());
        } else throw HttpException::getInstance(403);
    }

    /**
     * 계약 유형이 중계인 경우 하위 계약 정보를 설정한다.
     * @param SubContractDto $dto
     * @param Contract $contract
     * @return void
     * @throws HttpException
     */
    public function setSubContract(SubContractDto $dto, Contract $contract): void {
        if ($this->user->isOwnType(MemberType::TYPE_INTERMEDIARY) &&
            $contract->type == ContractType::INTERMEDIARY->value &&
            $this->user->id == $contract->recipient_user_id) {
            $contract->fill($dto->toArray());
            $contract->save();
        } else throw HttpException::getInstance(403);
    }

    /**
     * 계약정보를 갱신한다.
     * @param Contract $contract
     * @param ContractDto $dto
     * @return void
     * @throws HttpException
     */
    public function updateContract(Contract $contract, ContractDto $dto): void {
        if ($contract->order_user_id == $this->user) {
            $contract->fill($dto->toArray());
            $contract->save();
        } else throw HttpException::getInstance(403);
    }

    /**
     * 지정 계약정보를 삭제한다.
     * @param Contract $contract
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function deleteContract(Contract $contract): void {
        if ($contract->order_user_id != $this->user->id) throw HttpException::getInstance(403);
        if ($contract->status >= ContractStatus::PUBLISHED)
            throw HttpErrorsException::getInstance([__('errors.contract.cannot_remove')], 406);
        $contract->delete();
    }

    /**
     * 지정 계약의 관리기관 계정 일련번호 목록을 리턴한다. 발주자 또는 수주자 관리기관 유형은 접속한 사용자 계정에 따라 선택된다.
     * @param Contract $contract
     * @return array
     * @throws HttpException
     */
    public function getManagerUserId(Contract $contract): array {
        $contract_part = $this->getContractPart($contract, $this->user);
        if ($contract_part != ContractPartType::ORDER && $contract_part != ContractPartType::RECIPIENT)
            throw HttpException::getInstance(403);
        $type = $contract_part == ContractPartType::ORDER ?
            ContractPartType::ORDER_MANAGER : ContractPartType::RECIPIENT_MANAGER;
        $result = DB::table('contract_managers')
            ->where('contract_id', $contract->id)
            ->where('type', $type->value)
            ->get()->pluck('manager_user_id')->toArray();
        if (empty($result)) throw HttpException::getInstance(404);
        return $result;
    }

    /**
     * 계약 정보의 메니저 여부를 판단한다.
     * @param Contract $contract
     * @param User $user
     * @param ContractPartType $type
     * @return bool
     */
    public function isContractManager(Contract $contract, User $user, ContractPartType $type): bool {
        $cnt = DB::table('contract_managers')
            ->where('contract_id', $contract->id)
            ->where('manager_user_id', $user->id)
            ->where('type', $type)
            ->count();
        return $cnt > 0;
    }

    /**
     * 로그인 사용자(발주자 또는 수주자)의 관리기관 계정 일련번호를 검색하여 리턴한다.
     * @param array $manager_ids
     * @return array
     */
    private function _getManagerInPool(array $manager_ids) : array {
        return DB::table('managers')
            ->select('manager_user_id')
            ->where('organization_user_id', $this->user->id)
            ->whereIn('manager_user_id', $manager_ids)
            ->get()->pluck('manager_user_id')->toArray();
    }

    /**
     * 지정 계약 관리 기관 목록을 리턴한다.
     * @param Contract $contract
     * @param ContractPartType $type
     * @return array
     */
    private function _getContractManagers(Contract $contract, ContractPartType $type) : array {
        return DB::table('contract_managers')
            ->select('manager_user_id')
            ->where('contract_id', $contract->id)
            ->where('type', $type->value)
            ->get()->pluck('type')->toArray();
    }

    /**
     * 계약정보의 메니저로 등록한다.
     * @param Contract $contract
     * @param User $user
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function addContractManager(Contract $contract, User $user): void {
        $contract_part = $this->getContractPart($contract, $this->user);
        if ($contract_part != ContractPartType::ORDER && $contract_part != ContractPartType::RECIPIENT)
            throw HttpException::getInstance(403);
        $pool_users = $this->_getManagerInPool([$user->id]);
        if(empty($pool_users))
            throw HttpErrorsException::getInstance([__('errors.user.not_found')], 406);
        $type = match ($contract_part) {
            ContractPartType::ORDER => ContractPartType::ORDER_MANAGER,
            ContractPartType::RECIPIENT => ContractPartType::RECIPIENT_MANAGER,
        };

        DB::table('contract_managers')
            ->insert(['contract_id' => $contract->id, 'manager_user_id' => $user->id, 'type' => $type->value]);
    }

    /**
     * 지정 계약정보의 발주자 또는 수주자 측 관리기관을 설정한다. 기존 설정된 관리기관은 무시된다.
     * @param Contract $contract
     * @param array $manager_user_ids
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function setContractManagers(Contract $contract, array $manager_user_ids) : void {
        $contract_part = $this->getContractPart($contract, $this->user);
        if ($contract_part != ContractPartType::ORDER && $contract_part != ContractPartType::RECIPIENT)
            throw HttpException::getInstance(403);

        $type = match ($contract_part) {
            ContractPartType::ORDER => ContractPartType::ORDER_MANAGER,
            ContractPartType::RECIPIENT => ContractPartType::RECIPIENT_MANAGER,
            default => ContractPartType::NONE
        };

        $pool_users = $this->_getManagerInPool($manager_user_ids);
        if(empty($pool_users)) throw HttpErrorsException::getInstance([__('errors.user.not_found')], 406);
        $current_manager_user_ids = $this->_getContractManagers($contract, $type);
        $ignore_ids = array_intersect($pool_users, $current_manager_user_ids);
        $add_ids = array_diff($pool_users, $ignore_ids);
        $delete_ids = array_diff($current_manager_user_ids, $pool_users);

        DB::table('contract_managers')
            ->where('type', $type)
            ->whereIn('manager_user_id', $delete_ids)
            ->delete();

        $data = [];
        foreach($add_ids as $user_id)
            $data[] = ['contract_id' => $contract->id, 'manager_user_id' => $user_id, 'type' => $type->value];
        DB::table('contract_managers')->insert($data);
    }

    /**
     * 지정 계약 관리기관을 삭제한다.
     * @param Contract $contract
     * @param User $user
     * @return void
     * @throws HttpException
     */
    public function deleteContractManager(Contract $contract, User $user): void {
        $contract_part = $this->getContractPart($contract, $user);
        if ($contract_part != ContractPartType::ORDER && $contract_part != ContractPartType::RECIPIENT)
            throw HttpException::getInstance(403);
        $type = $contract_part == ContractPartType::ORDER ?
            ContractPartType::ORDER_MANAGER : ContractPartType::RECIPIENT_MANAGER;
        DB::table('contract_managers')
            ->where('contract_id', $contract->id)
            ->where('manager_user_id', $user->id)
            ->where('type', $type)
            ->delete();
    }

    /**
     * 지정 사용자가 계약정보의 어느 분야를 담당하는지 판단하여 리턴한다.
     * @param Contract $contract
     * @param User|null $user
     * @return ContractPartType|null
     * @throws HttpException
     */
    public function getContractPart(Contract $contract, ?User $user): ?ContractPartType {
        if (!$user) throw HttpException::getInstance(403);
        if ($contract->order_user_id == $user->id) return ContractPartType::ORDER;
        elseif ($contract->recipient_user_id == $user->id) return ContractPartType::RECIPIENT;
        elseif ($contract->sub_recipient_user_id == $user->id) return ContractPartType::RECIPIENT;
        elseif ($contract->mediation_user_id == $user->id) return ContractPartType::MEDIATION;
        else {
            if ($user->isOwnType(MemberType::TYPE_FOREIGN_MANAGER_OPERATOR) ||
                $user->isOwnType(MemberType::TYPE_MANAGER_OPERATOR))
                $contract_manager = $this->getContractManager($contract, User::findMe($user->management_org_id));
            else $contract_manager = $this->getContractManager($contract, $user);
            return ContractPartType::tryFrom($contract_manager->type) ?? ContractPartType::NONE;
        }
    }

    /**
     * 계약 구성원 중 발주자, 수주자의 계약의 사용 가능한 관리기관 유형을 리턴한다.
     * @param Contract $contract
     * @param ContractPartType $part
     * @param User $manager
     * @return ContractPartType|null
     */
    public function getAvailableManagerType(Contract $contract, ContractPartType $part, User $manager): ?ContractPartType {
        if ($part == ContractPartType::ORDER && $manager->isOwnType(MemberType::TYPE_MANAGER))
            return ContractPartType::ORDER_MANAGER;
        elseif ($part == ContractPartType::RECIPIENT && $manager->isOwnType(MemberType::TYPE_FOREIGN_MANAGER))
            return ContractPartType::RECIPIENT_MANAGER;
        else return ContractPartType::NONE;
    }

    /**
     * 계약 정보에 지정 사용자가 관리기관으로 등록된 경우 정보를 리턴한다.
     * @param Contract $contract
     * @param User $user
     * @return object|null
     */
    public function getContractManager(Contract $contract, User $user): ?object {
        return DB::table('contract_managers')
            ->where('contract_id', $contract->id)
            ->where('manager_user_id', $user->id)
            ->get()->first();
    }

    /**
     * 계약관련 파일을 등록한다.
     * @param Contract $contract
     * @param ContractFileDto $dto
     * @return void
     * @throws HttpException
     */
    public function addContractFile(Contract $contract, ContractFileDto $dto): void {
        if ($this->getContractPart($contract, $this->user) == ContractPartType::NONE &&
            $this->getContractPart($contract, $this->manager) == ContractPartType::NONE)
            throw HttpException::getInstance(403);
        ContractFile::create([
                'contract_id' => $contract->id,
                'upload_user_id' => $this->user->id,
            ] + $dto->toArray());
    }

    /**
     * 계약관련 지정 파일을 변경한다.
     * @param ContractFile $file
     * @param ContractFileDto $dto
     * @return void
     * @throws HttpException
     */
    public function updateContractFile(ContractFile $file, ContractFileDto $dto): void {
        if ($this->user->id == $file->user_id) {
            $file->title = $dto->getTitle();
            $file->file_group = $dto->getFileGroup()->value;
            $file->origin_name = $dto->getOriginName();
            if ($dto->getFilePath()) {
                Storage::disk('local')->delete($file->file_path);
                $file->file_path = $dto->getFilePath();
            }
            $file->save();
        } else throw HttpException::getInstance(403);
    }

    /**
     * 계약관련 파일 작성자가 파일을 삭제한다.
     * @param ContractFile $file
     * @return void
     * @throws HttpException
     */
    public function deleteContractFile(ContractFile $file): void {
        if ($this->user->id == $file->user_id) $file->delete();
        else throw HttpException::getInstance(403);
    }

    /**
     * 지정 계약정보에 배정된 근로자 목록을 리턴한다.
     * @param Contract $contract
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listAssignedWorker(Contract $contract, ListQueryParam $param): PageCollection {
        if ($this->getContractPart($contract, $this->user) == ContractPartType::NONE &&
            $this->getContractPart($contract, $this->manager) == ContractPartType::NONE)
            throw HttpException::getInstance(403);
        $query = AssignedWorker::orderBy($param->order, $param->direction)
            ->where('contract_id', $contract->id)
            ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                $query->where($param->field, $param->operator, $param->keyword);
            });
        $total = $query->count();
        $total_page = ceil($total / $param->page_per_items);
        $collection = $query->skip(($param->page - 1) * $param->page_per_items)->take($param->page_per_items)
            ->get();
        return new PageCollection($total, $total_page, $collection);
    }

    /**
     * 근로자 배정 가능 여부를 지정한다.
     * @param Contract $contract
     * @return bool
     */
    public function isAssignableWorker(Contract $contract): bool {
        return $contract->status >= ContractStatus::PUBLISHED->value &&
            $contract->status < ContractStatus::WORKER_DECISION->value;
    }

    /**
     * 수주처 계약 관리기관에서 근로자를 배정한다.
     * @param Contract $contract
     * @param array $ids
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function assignWorkers(Contract $contract, array $ids): void {
        if ($this->getContractPart($contract, $this->manager) == ContractPartType::RECIPIENT_MANAGER) {
            if (!$this->isAssignableWorker($contract))
                throw HttpErrorsException::getInstance([__('errors.contract.no_assignable')], 406);
            $assigned_ids = AssignedWorker::where('contract_id', $contract->id)
                ->whereIn('worker_user_id', $ids)
                ->get()->pluck('worker_user_id')->toArray();
            $insertable_worker_ids = array_diff($assigned_ids, $ids);
            if (!empty($insertable_worker_ids)) {
                foreach ($insertable_worker_ids as $id) {
                    AssignedWorker::create([
                        'contract_id' => $contract->id,
                        'worker_user_id' => $id,
                        'status' => AssignedWorkerStatus::REGISTERED->value
                    ]);
                }
            } else throw HttpErrorsException::getInstance([__('errors.user.already_exists')], 406);
        } else throw HttpException::getInstance(403);
    }

    /**
     * 수주 계약 관리 기관이 배정된 근로자 정보를 삭제한다.
     * @param Contract $contract
     * @param array $ids
     * @return void
     * @throws HttpException
     */
    public function deleteAssignedWorkers(Contract $contract, array $ids): void {
        if ($this->getContractPart($contract, $this->manager) == ContractPartType::RECIPIENT_MANAGER) {
            AssignedWorker::query()
                ->where('contract_id', $contract->id)
                ->whereIn('id', $ids)
                ->delete();
        } else throw HttpException::getInstance(403);
    }

    /**
     * 계약 구성원이 배정 근로자 상태정보를 변경한다.
     * @param Contract $contract
     * @param array $ids
     * @param AssignedWorkerStatus $status
     * @return void
     * @throws HttpException
     */
    public function updateAssignedWorkerStatus(Contract $contract, array $ids, AssignedWorkerStatus $status): void {
        if ($this->getContractPart($contract, $this->user) != ContractPartType::NONE ||
            $this->getContractPart($contract, $this->manager) != ContractPartType::NONE) {
            AssignedWorker::query()
                ->whereIn('id', $ids)
                ->update(['status' => $status->value]);
        }
    }

    /**
     * 발주자, 발주 계약 관리기관에게 근로자 채용 참여 기업 목록을 리턴한다.
     * @param Contract $contract
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listWorkingCompany(Contract $contract, ListQueryParam $param): PageCollection {
        if ($this->getContractPart($contract, $this->user) == ContractPartType::ORDER ||
            $this->getContractPart($contract, $this->manager) == ContractPartType::ORDER_MANAGER) {
            $query = WorkingCompany::orderBy($param->order, $param->direction)
                ->where('contract_id', $contract->id)
                ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                });
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $collection = $query->skip(($param->page - 1) * $param->page_per_items)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $collection);
        } else throw HttpException::getInstance(403);
    }

    /**
     * 채용 참여 기업과 계획 근로자 수를 등록한다.
     * @param Contract $contract
     * @param WorkingCompaniesDto $dto
     * @return void
     * @throws HttpException|Exception
     */
    public function addWorkingCompany(Contract $contract, WorkingCompaniesDto $dto): void {
        if ($this->getContractPart($contract, $this->user) == ContractPartType::ORDER_MANAGER) {
            $current_ids = $this->_getWorkingCompanyIds($contract);
            $ain = array_intersect($dto->getWorkingCompanyIds(), $current_ids);
            $new = array_diff($dto->getWorkingCompanyIds(), $ain);

            $data = [];
            foreach ($new as $id) $data[] = [
                'contract_id' => $contract->id,
                'company_user_id' => $id,
                'planned_worker_count' => $dto->getPlannedWorkerCount()
            ];
            WorkingCompany::insert($data);
        } else throw HttpException::getInstance(403);
    }

    /**
     * 채용계약에 참여하는 기업 계정 ID 목록을 리턴한다.
     * @param Contract $contract
     * @return array
     */
    private function _getWorkingCompanyIds(Contract $contract): array {
        return WorkingCompany::where('contract_id', $contract->id)
            ->get()->pluck('company_user_id')->toArray();
    }

    /**
     * 지정 채용 계획정보를 수정한다.
     * @param Contract $contract
     * @param UpdatePlannedWorkerCountDto $dto
     * @return void
     * @throws HttpException|Exception
     */
    public function updatePlannedWorkerCount(Contract $contract, UpdatePlannedWorkerCountDto $dto): void {
        if ($this->getContractPart($contract, $this->user) == ContractPartType::ORDER_MANAGER) {
            WorkingCompany::where('contract_id', $contract->id)
                ->whereIn('id', $dto->getIds())
                ->update([
                    'planned_worker_count' => $dto->getPlannedWorkerCount()
                ]);
        } else throw HttpException::getInstance(403);
    }

    /**
     * 지정 채용 계획정보를 삭제한다.
     * @param Contract $contract
     * @param array $ids
     * @return void
     * @throws HttpException
     */
    public function deleteWorkingCompanies(Contract $contract, array $ids): void {
        if ($this->getContractPart($contract, $this->user) == ContractPartType::ORDER_MANAGER) {
            WorkingCompany::where('contract_id', $contract->id)
                ->whereIn('id', $ids)
                ->delete();
        } else throw HttpException::getInstance(403);
    }

    /**
     * 지정 계약에 해당 기업이 채용기업으로 참여하는지 여부를 판단한다.
     * @param Contract $contract
     * @param User $company
     * @return bool
     */
    private function _isWorkingCompanyOfContract(Contract $contract, User $company): bool {
        return (WorkingCompany::where('contract_id', $contract->id)
                ->where('company_user_id', $company->id)
                ->count()) > 0;
    }

    /**
     * 지정 기업이 채용 계획중인 근로자 수를 리턴한다.
     * @param Contract $contract
     * @param AssignCompanyDto $dto
     * @return int
     */
    private function _getPlannedWorkerCount(Contract $contract, AssignCompanyDto $dto): int {
        return WorkingCompany::where('contract_id', $contract->id)
            ->where('company_user_id', $dto->getCompany()->id)
            ->get()->first()->planned_worker_count;
    }

    /**
     * 지정 기업에 배정된 근로자 수를 리턴한다.
     * @param Contract $contract
     * @param int $id
     * @return int
     */
    private function _getAssignedWorkerCountForCompany(Contract $contract, int $id): int {
        return AssignedWorker::where('contract_id', $contract)
            ->where('company_user_id', $id)
            ->count();
    }

    /**
     * 지정 근로자들을 모두 지정 기업에 배정 가능한지 여부를 판단한다.
     * @param Contract $contract
     * @param AssignCompanyDto $dto
     * @return bool
     */
    public function isAssignAble(Contract $contract, AssignCompanyDto $dto): bool {
        $current = $this->_getAssignedWorkerCountForCompany($contract, $dto->getCompany()->id);
        $planned = $this->_getPlannedWorkerCount($contract, $dto);
        return ($current + $dto->getWorkerCount()) < $planned;
    }

    /**
     * 지정 회사의 배정 근로자 수 정보를 갱신한다.
     * @param Contract $contract
     * @param int $id
     * @return void
     */
    private function _updateAssignedWorkerCountFromCompany(Contract $contract, int $id): void {
        $working_company = WorkingCompany::where('contract_id', $contract->id)
            ->where('company_user_id', $id)
            ->get()->first();
        if ($working_company) {
            $working_company->assigned_worker_count = $this->_getAssignedWorkerCountForCompany($contract, $id);
            $working_company->save();
        }
    }

    /**
     * 근로자를 지정 기업에 배정한다.
     * @param Contract $contract
     * @param AssignCompanyDto $dto
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function assignCompany(Contract $contract, AssignCompanyDto $dto): void {
        if ($this->getContractPart($contract, $this->user) == ContractPartType::ORDER_MANAGER) {
            if (!$this->_isWorkingCompanyOfContract($contract, $dto->getCompany()))
                throw HttpErrorsException::getInstance([__('errors.contract.no_assignable')], 406);
            if (!$this->isAssignAble($contract, $dto)) throw HttpException::getInstance(468);

            AssignedWorker::where('contract_id', $contract->id)
                ->whereIn('id', $dto->getAssignedWorkerIds())
                ->update([
                    'company_user_id' => $dto->getCompany()->id,
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                ]);
            $this->_updateAssignedWorkerCountFromCompany($contract, $dto->getCompany()->id);
        } else throw HttpException::getInstance(403);
    }

    /**
     * 배정 근로자 정보들로부터 배정 기업을 추출한다.
     * @param array $ids
     * @return array
     */
    private function _getCompanyUserIdFromAssignedWorker(array $ids): array {
        $assigned_workers = AssignedWorker::whereIn('id', $ids);
        $company_ids = [];
        foreach ($assigned_workers as $worker) {
            if (!isset($company_ids[$worker->company_user_id])) $company_ids[$worker->company_user_id] = true;
        }
        return array_keys($company_ids);
    }

    /**
     * 지정 근로자들을 기업 배정을 해제한다.
     * @param Contract $contract
     * @param UnAssignedCompanyDto $dto
     * @return void
     * @throws HttpException
     */
    public function unAssignCompany(Contract $contract, UnAssignedCompanyDto $dto): void {
        if ($this->getContractPart($contract, $this->user) == ContractPartType::ORDER_MANAGER) {
            $company_ids = $this->_getCompanyUserIdFromAssignedWorker($dto->getAssignedWorkerIds());
            $now = Carbon::now();
            AssignedWorker::where('contract_id', $contract->id)
                ->whereIn('id', $dto->getAssignedWorkerIds())
                ->updaate([
                    'company_user_id' => null,
                    'updated_at' => $now->format('Y-m-d H:i:s')
                ]);
            foreach ($company_ids as $id) $this->_updateAssignedWorkerCountFromCompany($contract, $id);
        } else throw HttpException::getInstance(403);
    }

    /**
     * 지정 계약의 입국일정 정보 목록을 리턴한다.
     * @param Contract $contract
     * @param ListQueryParam $param
     * @return PageCollection
     */
    public function listEntrySchedule(Contract $contract, ListQueryParam $param) : PageCollection {
        $query = EntrySchedule::where('contract_id', $contract->id)
            ->orderBy($param->order, $param->direction)
            ->when($param->field && $param->keyword, function(Builder $query) use ($param) {
                $query->where($param->field, $param->operator, $param->keyword);
            });
        $total = $query->count();
        $total_page = ceil($total / $param->page_per_items);
        $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
            ->get();
        return new PageCollection($total, $total_page, $collection);
    }

    /**
     * 지정 계약의 근로자 입국일정을 추가한다.
     * @param Contract $contract
     * @param EntryScheduleDto $dto
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function addEntrySchedule(Contract $contract, EntryScheduleDto $dto) : void {
        if($this->user && $this->getContractPart($contract, $this->user) == ContractPartType::ORDER_MANAGER) {
            EntrySchedule::create([
                'contract_id' => $contract->id
            ] + $dto->toArray());
        }
        else throw HttpErrorsException::getInstance([__('errors.auth.no_permission')], 406);
    }

    /**
     * 지정 입국일정 정보를 갱신한다.
     * @param EntrySchedule $schedule
     * @param EntryScheduleDto $dto
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function updateEntrySchedule(EntrySchedule $schedule, EntryScheduleDto $dto) : void {
        $contract = Contract::findMe($schedule->contract_id);
        if($this->user && $this->getContractPart($contract, $this->user) == ContractPartType::ORDER_MANAGER) {
            $schedule->fill($dto->toArray());
            $schedule->save();
        }
        else throw HttpErrorsException::getInstance([__('errors.auth.no_permission')], 406);
    }

    /**
     * 지정 입국일정에 입국하는 근로자 수를 리턴한다.
     * @param EntrySchedule $schedule
     * @param array|null $exclude_ids
     * @return int
     */
    private function _getWorkerCountForEntrySchedule(EntrySchedule $schedule, array $exclude_ids = null) : int {
        return AssignedWorker::query()
            ->when($exclude_ids, function(Builder $query) use($exclude_ids) {
                $query->whereNotIn('id', $exclude_ids);
            })
            ->where('entry_schedule_id', $schedule->id)
            ->count();
    }

    /**
     * 지정 입국일정정보를 삭제한다. 만일 지정 일정에 입국하는 근로자가 있다면 삭제할 수 없다.
     * @param EntrySchedule $schedule
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function deleteEntrySchedule(EntrySchedule $schedule) : void {
        $contract = Contract::findMe($schedule->contract_id);
        if($this->user &&
            $this->getContractPart($contract, $this->user) == ContractPartType::ORDER_MANAGER &&
            $this->_getWorkerCountForEntrySchedule($schedule) == 0) {
            $schedule->delete();
        }
        else throw HttpErrorsException::getInstance([__('errors.auth.no_permission')], 406);
    }

    /**
     * 근로자의 입국일정을 변경한다. 만일 해당 입국일정에 정원이 초과되면 설정할 수 없다.
     * @param Contract $contract
     * @param WorkerEntryScheduleDto $dto
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function setWorkerEntrySchedule(Contract $contract, WorkerEntryScheduleDto $dto) : void {
        if($this->user && $this->getContractPart($contract, $this->user) == ContractPartType::RECIPIENT_MANAGER) {
            if(($this->_getWorkerCountForEntrySchedule($dto->getEntrySchedule(), $dto->getWorkerIds()) +
                    count($dto->getWorkerIds())) <= $dto->getEntrySchedule()->entry_limit) {
                AssignedWorker::query()
                    ->whereIn('id', $dto->getWorkerIds())
                    ->update(['entry_schedule_id' => $dto->getEntrySchedule()->id]);
            }
            else throw HttpException::getInstance(468);
        }
        else throw HttpErrorsException::getInstance([__('errors.auth.no_permission')], 406);
    }

    /**
     * 지정 계약의 근로자 평가 정보를 리턴한다.
     * @param Contract $contract
     * @return EvalInfo|null
     */
    private function _getContractWorkerEvaluation(Contract $contract) : ?EvalInfo {
        return EvalInfo::query()
            ->select('eval_infos.*')
            ->join('worker_eval_plans', 'eval_infos.id', '=', 'worker_eval_info_id')
            ->where('worker_eval_plans.contract_id', $contract->id)
            ->get()->first();
    }

    /**
     * 지정 계약의 기업평가 정보를 리턴한다.
     * @param Contract $contract
     * @return EvalInfo|null
     */
    private function _getContractCompanyEvaluation(Contract $contract) : ?EvalInfo {
        return EvalInfo::query()
            ->select('eval_infos.*')
            ->join('company_eval_plans', 'eval_infos.id', '=', 'company_eval_info_id')
            ->where('company_eval_plans.contract_id', $contract->id)
            ->get()->first();
    }


    /**
     * 지정 계약의 근로자 평가정보를 설정한다.
     * @param Contract $contract
     * @param EvalInfo $info
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function setWorkerEvaluationPlan(Contract $contract, EvalInfo $info) : void {
        if ($this->user && $this->getContractPart($contract, $this->user) == ContractPartType::ORDER_MANAGER) {
            if(!$this->_getContractWorkerEvaluation($contract)) {
                DB::table('worker_eval_plans')
                    ->where('contract_id', $contract->id)
                    ->update(['worker_eval_info_id' => $info->id]);
            }
            else {
                DB::table('worker_eval_plans')
                    ->insert([
                        'contract_id' => $contract->id,
                        'worker_eval_info_id' => $info->id
                    ]);
            }
        }
        else throw HttpErrorsException::getInstance([__('errors.auth.no_permission')], 406);
    }

    /**
     * 지정 개약의 기업평가 정보를 설정한다.
     * @param Contract $contract
     * @param EvalInfo $info
     * @return void
     * @throws HttpException
     */
    public function setCompanyEvaluationPlan(Contract $contract, EvalInfo $info) : void {
        if ($this->user && $this->getContractPart($contract, $this->user) == ContractPartType::ORDER_MANAGER) {
            if(!$this->_getContractCompanyEvaluation($contract)) {
                DB::table('company_eval_plans')
                    ->where('contract_id', $contract->id)
                    ->update(['company_eval_info_id' => $info->id]);
            }
            else {
                DB::table('company_eval_plans')
                    ->insert([
                        'contract_id' => $contract->id,
                        'company_eval_info_id' => $info->id
                    ]);
            }
        }
    }
}
