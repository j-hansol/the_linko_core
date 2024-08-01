<?php

namespace App\Services\V1;

use App\DTOs\V1\ActionPointDto;
use App\DTOs\V1\TaskDto;
use App\Events\WorkerActionPointChanged;
use App\Http\QueryParams\ListQueryParam;
use App\Lib\AssignedWorkerStatus;
use App\Lib\MemberType;
use App\Lib\PageCollection;
use App\Models\ActionPoint;
use App\Models\AssignedWorker;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkerActionPoint;
use App\Models\WorkerActionPointHistory;
use App\Services\Common\HttpException;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class CompanyService {
    protected ?User $user;

    function __construct() {
        $this->user = current_user();
    }

    /**
     * 서비스 프로바이더를 통해 인스턴스를 가져온다.
     * @return CompanyService
     * @throws Exception
     */
    public static function getInstance(): CompanyService {
        $instance = app(static::class);
        if (!$instance) throw new Exception('service not constructed');
        return $instance;
    }

    /**
     * 기업에 근무중이거나 근무했던 근로자 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listTask(ListQueryParam $param) : PageCollection {
        if($this->user->isOwnType(MemberType::TYPE_COMPANY)) {
            $query = Task::query()
                ->orderBy($param->order, $param->direction)
                ->where('company_user_id', $this->user->id)
                ->when($param->field && $param->keyword, function(Builder $query) use($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                });
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $result = $query->skip($param->start_rec_no)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $result);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 새로운 업무정보를 등록한다.
     * @param TaskDto $dto
     * @return void
     * @throws HttpException
     */
    public function addTask(TaskDto $dto) : void {
        if($this->user->isOwnType(MemberType::TYPE_COMPANY)) {
            Task::create(['company_user_id' => $this->user->id] = $dto->toArray());
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 영상 파일 내용을 리턴한다.
     * @param Task $task
     * @return mixed
     * @throws HttpException
     */
    public function showTaskMovie(Task $task) : mixed {
        if ($task->movie_file_path) return show_file('local', $task->movie_file_path);
        else throw HttpException::getInstance(404);
    }

    /**
     * 기업의 업무정보를 리턴한다.
     * @param Task $task
     * @return Task
     * @throws HttpException
     */
    public function getTask(Task $task) : Task {
        if($this->user->id == $task->company_user_id) return $task;
        else throw HttpException::getInstance(403);
    }

    /**
     * 기업의 기존 업무정보를 변경한다.
     * @param TaskDto $dto
     * @param Task $task
     * @return void
     * @throws HttpException
     */
    public function updateTask(TaskDto $dto, Task $task) : void {
        if($this->user->id == $task->company_user_id) {
            if($dto->getDeletePrevMovie() && $task->movie_file_path)
                Storage::disk('local')->delete($task->movie_file_path);
            $task->fill($dto->toArray());
            $task->save();
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 지정 업무정보를 삭제한다.
     * @param Task $task
     * @return void
     * @throws HttpException
     */
    public function deleteTask(Task $task) : void {
        if($this->user->id == $task->company_user_id) $task->delete();
        else throw HttpException::getInstance(403);
    }

    /**
     * 기업의 기본 활동 지점 정보 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listActionPoint(ListQueryParam $param) : PageCollection {
        if($this->user->isOwnType(MemberType::TYPE_COMPANY)) {
            $query = ActionPoint::query()
                ->orderBy($param->order, $param->direction)
                ->where('company_user_id', $this->user->id)
                ->when($param->field && $param->keyword, function(Builder $query) use($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                });
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $result = $query->skip($param->start_rec_no)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $result);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 기업의 기본 활동 지점 목록을 등록한다.
     * @param ActionPointDto $dto
     * @return void
     * @throws HttpException
     */
    public function addActionPoint(ActionPointDto $dto) : void {
        if($this->user->isOwnType(MemberType::TYPE_COMPANY)) {
            ActionPoint::create(
                ['company_user_id' => $this->user->id] + $dto->toArray()
            );
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 기업의 기본 활동지점 정보를 리턴한다.
     * @param ActionPoint $point
     * @return ActionPoint
     * @throws HttpException
     */
    public function getActionPoint(ActionPoint $point) : ActionPoint {
        if($this->user->id == $point->company_user_id) return $point;
        else throw HttpException::getInstance(403);
    }

    /**
     * 기업의 기본 활동지점 정보를 변경한다.
     * @param ActionPointDto $dto
     * @param ActionPoint $point
     * @return void
     * @throws HttpException
     */
    public function updateActionPoint(ActionPointDto $dto, ActionPoint $point) : void {
        if($this->user->id == $point->company_user_id) {
            $point->fill($dto->toArray());
            $point->save();
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 기업의 기본 활동지점 정보를 삭제한다.
     * @param ActionPoint $point
     * @return void
     * @throws HttpException
     */
    public function deleteActionPoint(ActionPoint $point) : void {
        if($this->user->id == $point->company_user_id) $point->delete();
        else throw HttpException::getInstance(403);
    }

    /**
     * 기업에 근무중이거나 군무했던 근로자 배정 정보 목록읅 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listWorker(ListQueryParam $param) : PageCollection {
        if($this->user->isOwnType(MemberType::TYPE_COMPANY)) {
            $query = AssignedWorker::query()
                ->orderBy($param->order, $param->direction)
                ->where('company_user_id', $this->user->id)
                ->when($param->field && $param, function(Builder $query) use($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                });
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $result = $query->skip($param->start_rec_no)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $result);
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 배정 근로자 정보를 리턴한다.
     * @param AssignedWorker $worker
     * @return array
     * @throws HttpException
     */
    public function getWorker(AssignedWorker $worker) : array {
        if($this->user->id == $worker->company_user_id) return $worker->toWorkerInfoArray();
        else throw HttpException::getInstance(403);
    }

    /**
     * 지정 배정 근로자의 활동 지점 목록을 리턴한다.
     * @param AssignedWorker $worker
     * @return Collection
     * @throws HttpException
     */
    public function listWorkerActionPoint(AssignedWorker $worker) : Collection {
        if($this->user->id == $worker->company_user_id) {
            return WorkerActionPoint::query()
                ->where('assigned_worker_id', $worker->id)
                ->get();
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근무중인 근로자의 활동 지점 정보를 설정한다.
     * @param ActionPointDto $dto
     * @param AssignedWorker $worker
     * @return void
     * @throws HttpException
     */
    public function setWorkerActionPoint(ActionPointDto $dto, AssignedWorker $worker) : void {
        if($this->user->isOwnType(MemberType::TYPE_COMPANY) &&
            $worker->company_user_id == $this->user->id && $worker->status != AssignedWorkerStatus::LEAVED) {
            $current_point = WorkerActionPoint::getWorkerActionPoint($worker, $dto->getType());
            if($current_point) {
                $current_point->fill(['author_user_id' => $this->user->id] + $dto->toArray());
                $current_point->save();
                $history = WorkerActionPointHistory::createFrom($current_point);
                WorkerActionPointChanged::dispatch($history);
            }
            else {
                $new = WorkerActionPoint::create([
                    'contract_id' => $worker->contract_id,
                    'assigned_worker_id' => $worker->id,
                    'company_user_id' => $this->user->id,
                    'worker_user_id' => $worker->worker_user_id,
                    'author_user_id' => $this->user->id
                ] + $dto->toArray());
                $history = WorkerActionPointHistory::createFrom($new);
                WorkerActionPointChanged::dispatch($history);
            }
        }
        else throw HttpException::getInstance(403);
    }
}
