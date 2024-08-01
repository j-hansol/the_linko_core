<?php

namespace App\Services\V2;

use App\DTOs\Common\IdDto;
use App\DTOs\V2\RecommendedWorkerStatusDto;
use App\DTOs\V2\WorkerRecommendationDto;
use App\DTOs\V2\WorkerRecommendationForOperatorDto;
use App\DTOs\V2\WorkerRecommendationRequestDto;
use App\DTOs\V2\WorkerRecommendationRequestStatusDto;
use App\Http\QueryParams\ListQueryParam;
use App\Lib\MemberType;
use App\Lib\PageCollection;
use App\Lib\RecommendedWorkerStatus;
use App\Lib\WorkerRecommendationRequestStatus;
use App\Models\RecommendedWorker;
use App\Models\User;
use App\Models\WorkerRecommendation;
use App\Models\WorkerRecommendationRequest;
use App\Models\WorkerRecommendationTarget;
use App\Services\Common\HttpException;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class WorkerRecommendationService {
    protected ?User $user;
    protected bool $is_logged_in = false;
    protected bool $is_operator = false;

    function __construct() {
        $this->user = current_user();
        $this->is_logged_in = $this->user && access_token();
        $this->is_operator = $this->user->isOwnType(MemberType::TYPE_OPERATOR);
    }

    /**
     * 서비스 프로바이더를 통해 인스턴스를 가져온다.
     * @return UserService
     * @throws Exception
     */
    public static function getInstance() : static {
        $instance = app(static::class);
        if(!$instance) throw new Exception('service not constructed');
        return $instance;
    }

    /**
     * 모든 추천 요청 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     */
    public function listRequest(ListQueryParam $param) : PageCollection {
        $query = WorkerRecommendationRequest::orderBy($param->order, $param->direction)
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
     * 로그인 사용자 본인이 추천 요청 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listRequestForUser(ListQueryParam $param) : PageCollection {
        if($this->is_logged_in) {
            $query = WorkerRecommendationRequest::orderBy($param->order, $param->direction)
                ->where('user_id', $this->user->id)
                ->when($param->field && $param->keyword, function(Builder $query) use ($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                });
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $collection);
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 회원 본인이 추천 요청 정보를 등록한다.
     * @param WorkerRecommendationRequestDto $dto
     * @return void
     * @throws HttpException
     */
    public function addRequest(WorkerRecommendationRequestDto $dto) : void {
        if($this->is_logged_in) {
            WorkerRecommendationRequest::create([
                'user_id' => $this->user->id,
                'status' => WorkerRecommendationRequestStatus::REGISTERED->value
            ] + $dto->toArray());
        }
        else throw HttpException::getInstance(401);
    }


    /**
     * 회원 본인 또는 관리자가 추천 요청정보를 리턴한다.
     * @param WorkerRecommendationRequest $request
     * @return WorkerRecommendationRequest
     * @throws HttpException
     */
    public function getWorkerRecommendationRequest(
        WorkerRecommendationRequest $request
    ) : WorkerRecommendationRequest {
        if($this->is_logged_in) return $request;
        else throw HttpException::getInstance(401);
    }

    /**
     * 회원 본인 또는 운영 관리자가 요청 정보를 수정한다.
     * @param WorkerRecommendationRequestDto $dto
     * @param WorkerRecommendationRequest $request
     * @return void
     * @throws HttpException
     */
    public function updateWorkerRecommendationRequest(
        WorkerRecommendationRequestDto $dto,
        WorkerRecommendationRequest $request
    ) : void {
        if($this->is_logged_in) {
            if($this->user->id == $request->user_id || $this->is_operator) {
                $request->fill($dto->toArray());
                $request->save();
            }
            else throw HttpException::getInstance(403);
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 본인 또는 운영 관리자가 요청 정보를 삭제한다.
     * @param WorkerRecommendationRequest $request
     * @return void
     * @throws HttpException
     */
    public function deleteWorkerRecommendationRequest(WorkerRecommendationRequest $request) : void {
        if($this->is_logged_in) {
            if($this->user->id == $request->user_id || $this->is_operator) $request->delete();
            else throw HttpException::getInstance(403);
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 추천 요청 상태를 설정한다.
     * @param WorkerRecommendationRequestStatusDto $dto
     * @param WorkerRecommendationRequest $request
     * @return void
     */
    public function setRequestStatus(
        WorkerRecommendationRequestStatusDto $dto,
        WorkerRecommendationRequest $request) : void {
        $request->status = $dto->getStatus();
        $request->save();
    }

    /**
     * 추천 요청을 승인한다.
     * @param WorkerRecommendationDto $dto
     * @param WorkerRecommendationRequest $request
     * @return void
     * @throws HttpException
     */
    public function setRecommendation(
        WorkerRecommendationDto $dto, WorkerRecommendationRequest $request) : void {
        if($this->is_operator) {
            DB::beginTransaction();
            try {
                $recommendation = $request->getRecommendation();
                if(!$recommendation) {
                    $recommendation = WorkerRecommendation::create([
                            'worker_recommendation_request_id' => $request->id,
                            'user_id' => $this->user->id,
                        ] + $dto->toArray());
                    $request->status = WorkerRecommendationRequestStatus::ACCEPTED->value;
                    $request->save();

                    $target_user_ids = $dto->getTargetUserIds();
                    if(empty($target_user_ids)) {
                        WorkerRecommendationTarget::create([
                            'worker_recommendation_id' => $recommendation->id,
                            'user_id' => $request->user_id
                        ]);
                    }
                    else $this->_syncTargetUserIds($target_user_ids, $recommendation);
                }
                else {
                    $recommendation->fill($dto->toArray());
                    $recommendation->save();
                    $this->_syncTargetUserIds($dto->getTargetUserIds(), $recommendation);
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
     * 모든 공유 정보 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listRecommendation(ListQueryParam $param) : PageCollection {
        if($this->is_operator) {
            $query = WorkerRecommendation::orderBy($param->order, $param->direction)
                ->when($param->field && $param->keyword, function(Builder $query) use($param) {
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
     * 회원 본인에게 공유된 공유 정보 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listRecommendationForUser(ListQueryParam $param) : PageCollection {
        if($this->is_logged_in) {
            $query = WorkerRecommendation::query()
                ->select('worker_recommendations.*')
                ->join('worker_recommendation_targets',
                    'worker_recommendations.id', '=',
                    'worker_recommendation_targets.user_id')
                ->orderBy($param->order, $param->direction)
                ->where('worker_recommendation_targets.user_id', $this->user->id)
                ->when($param->field && $param->keyword, function(Builder $query) use($param) {
                    $query->where($param->field, $param->operator, $param->keyword);
                });
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
                ->get();
            return new PageCollection($total, $total_page, $collection);
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 운영 관리자가 직접 추천정보를 등록ㄷ한다.
     * @param WorkerRecommendationForOperatorDto $dto
     * @return void
     * @throws HttpException
     */
    public function addWorkerRecommendation(WorkerRecommendationForOperatorDto $dto) : void {
        if($this->is_operator) {
            DB::beginTransaction();
            $targetUserIds = $dto->getTargetUserIds();
            try {
                $request = WorkerRecommendationRequest::create([
                    'user_id' => reset($targetUserIds),
                    'status' => WorkerRecommendationRequestStatus::ACCEPTED->value
                ] + $dto->toRequestArray());
                $recommendation = WorkerRecommendation::create([
                    'worker_recommendation_request_id' => $request->id,
                    'user_id' => $this->user->id,
                ] + $dto->toRecommendationArray());
                $this->_syncTargetUserIds($targetUserIds, $recommendation);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 기존 타겟 사용자정보를 갱신한다.
     * @param array $ids
     * @param WorkerRecommendation $recommendation
     * @return void
     */
    private function _syncTargetUserIds(array $ids, WorkerRecommendation $recommendation) : void {
        $prev = WorkerRecommendationTarget::query()
            ->where('worker_recommendation_id', $recommendation->id)
            ->get()->pluck('user_id')->toArray();
        $deletable_target = array_diff($prev, $ids);
        $addable_target = array_diff($ids, $prev);
        WorkerRecommendationTarget::query()
            ->where('worker_recommendation_id', $recommendation->id)
            ->whereIn('user_id', $deletable_target)->delete();
        foreach($addable_target as $id) {
            WorkerRecommendationTarget::create([
                'worker_recommendation_id' => $recommendation->id,
                'user_id' => $id
            ]);
        }
    }

    /**
     * 지정 추천정보를 학제한다.
     * @param WorkerRecommendation $recommendation
     * @return void
     */
    public function deleteWorkerRecommendation(WorkerRecommendation $recommendation) : void {
        $recommendation->delete();
    }

    public function setWorkerRecommendationStatus(int $active, WorkerRecommendation $recommendation) : void {
        $recommendation->active = $active;
        $recommendation->save();
    }

    public function getWorkerRecommendation(WorkerRecommendation $recommendation) : WorkerRecommendation {
        return $recommendation;
    }

    /**
     * 지정 추천정보에 등록된 근로자 정보를 리턴한다.
     * @param ListQueryParam $param
     * @param WorkerRecommendation $recommendation
     * @return PageCollection
     * @throws HttpException
     */
    public function listRecommendedWorker(ListQueryParam $param, WorkerRecommendation $recommendation) : PageCollection {
        if($recommendation->active == 1) {
            $query = RecommendedWorker::query()
                ->where('worker_recommendation_id', $recommendation->id);
            $total = $query->count();
            $total_page = ceil($total / $param->page_per_items);
            $result = $query->skip($param->start_rec_no)
                ->take($param->page_per_items)->get();
            return new PageCollection($total, $total_page, $result);
        }
        else throw HttpException::getInstance(406);
    }

    public function getRecommendedWorkerInfo(RecommendedWorker $worker) : RecommendedWorker {
        return $worker;
    }

    /**
     * 추천 근로자를 추가한다.
     * @param IdDto $dto
     * @param WorkerRecommendation $recommendation
     * @return void
     * @throws Exception
     */
    public function addRecommendedWorkers(IdDto $dto, WorkerRecommendation $recommendation) : void {
        $target_ids = array_diff($dto->getIds(), $this->_getAddedWorkersInArray($dto, $recommendation));
        $data = [];
        foreach ($target_ids as $id) {
            $data[] = [
                'worker_recommendation_id' => $recommendation->id,
                'worker_user_id' => $id,
                'status' => RecommendedWorkerStatus::RECOMMENDED->value
            ];
        }

        DB::beginTransaction();
        try {
            DB::table('recommended_workers')
                ->insert($data);
            DB::commit();;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 지증 근로자 중 이미 등록된 근로자 정보를 배열로 리턴한다.
     * @param IdDto $dto
     * @param WorkerRecommendation $recommendation
     * @return array
     */
    private function _getAddedWorkersInArray(IdDto $dto, WorkerRecommendation $recommendation) : array {
        return RecommendedWorker::query()
            ->where('worker_recommendation_id', $recommendation->id)
            ->whereIn('worker_user_id', $dto->getIds())
            ->get()->pluck('worker_user_id')->toArray();
    }

    /**
     * 지정 추천 근로자 정보를 삭제한다.
     * @param IdDto $dto
     * @param WorkerRecommendation $recommendation
     * @return void
     */
    public function deleteRecommendedWorkers(IdDto $dto, WorkerRecommendation $recommendation) : void {
        DB::table('recommended_workers')
            ->whereIn('id', $dto->getIds())
            ->delete();
    }

    /**
     * 지정 추천 근로자의 상태를 변경한다.
     * @param RecommendedWorkerStatusDto $dto
     * @param RecommendedWorker $worker
     * @return void
     */
    public function setRecommendedWorkerStatus(RecommendedWorkerStatusDto $dto, RecommendedWorker $worker) : void {
        $worker->status = $dto->getStatus();
        $worker->save();
    }
}
