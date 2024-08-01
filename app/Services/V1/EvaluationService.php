<?php

namespace App\Services\V1;

use App\DTOs\V1\EvaluationAnswerDto;
use App\Http\QueryParams\ListQueryParam;
use App\Lib\AssignedWorkerStatus;
use App\Lib\PageCollection;
use App\Models\AssignedWorker;
use App\Models\Evaluation;
use App\Models\User;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use Exception;
use Illuminate\Database\Eloquent\Builder;

class EvaluationService {
    protected ?User $user;

    function __construct() {
        $this->user = current_user();
    }

    /**
     * 서비스 프로바이더를 통해 인스턴스를 가져온다.
     * @return EvaluationService
     * @throws Exception
     */
    public static function getInstance(): EvaluationService {
        $instance = app(static::class);
        if (!$instance) throw new Exception('service not constructed');
        return $instance;
    }

    /**
     * 근로자 평가 가능 여부를 판단한다.
     * @param AssignedWorker $assignedWorker
     * @return bool
     */
    private function isAbleEvaluation(AssignedWorker $assignedWorker) : bool {
        return (
            ($assignedWorker->status == AssignedWorkerStatus::WORKING->value ||
                $assignedWorker->status == AssignedWorkerStatus::EVALUATION->value) &&
            ($assignedWorker->company_user_id == $this->user->id ||
                $assignedWorker->worker_user_id == $this->user->id)
        );
    }

    /**
     * 기업평가 가능한 근로자 배정 정보를 리턴한다.
     * @param User $company
     * @return AssignedWorker|null
     */
    private function getAssignedWorker(User $company) : ?AssignedWorker {
        return $assigned_worker = AssignedWorker::where('company_user_id', $company->id)
            ->whereIn('status', [AssignedWorkerStatus::WORKING->value, AssignedWorkerStatus::EVALUATION->value])
            ->get()->first();
    }

    /**
     * 지정 기업이 작성한 근로자 평가정보 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listEvaluation(ListQueryParam $param) : PageCollection {
        if($this->user->is_ogranization == 1) {
            $query = Evaluation::orderBy($param->order, $param->direction)
                ->where('user_id', $this->user->id)
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
     * 평가 대상 계정 일련번호를 리턴한다.
     * @param AssignedWorker $assignedWorker
     * @return int|null
     */
    private function _getTargetUserId(AssignedWorker $assignedWorker) : ?int {
        if($this->user->id == $assignedWorker->worker_user_id) return $assignedWorker->company_user_id;
        elseif($this->user->id == $assignedWorker->company_user_id) return $assignedWorker->worker_user_id;
        else return null;
    }

    /**
     * 지정 근로자 평가정보를 등록한다.
     * @param EvaluationAnswerDto $dto
     * @param AssignedWorker $assignedWorker
     * @return void
     * @throws HttpException
     */
    public function addEvaluation(EvaluationAnswerDto $dto, AssignedWorker $assignedWorker) : void {
        if($this->isAbleEvaluation($assignedWorker)) {
            Evaluation::create([
                'contract_id' => $assignedWorker->contract_id,
                'user_id' => $this->user->id,
                'target_user_id' => $this->_getTargetUserId($assignedWorker),
                'assigned_worker_id' => $assignedWorker->id
            ] + $dto->toArray());
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 근로자 또는 기업 본인인이 작성한 평가정보를 리턴한다.
     * @param Evaluation $evaluation
     * @return Evaluation
     * @throws HttpException
     */
    public function getEvaluation(Evaluation $evaluation) : Evaluation {
        if($this->user->id == $evaluation->user_id) {
            return $evaluation;
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 지정 평가정보를 갱신한다.
     * @param EvaluationAnswerDto $dto
     * @param Evaluation $evaluation
     * @return void
     * @throws HttpErrorsException
     * @throws HttpException
     */
    public function updateEvaluation(EvaluationAnswerDto $dto, Evaluation $evaluation) : void {
        if($this->user->id == $evaluation->user_id) {
            if($evaluation->eval_info_id == $dto->getEvalInfoId())
                throw HttpErrorsException::getInstance([__('errors.eval_info.no_match_eval_info_id')], 400);
            $evaluation->fill($dto->toArray());
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 지정 평가정보를 삭제한다.
     * @param Evaluation $evaluation
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function deleteEvaluation(Evaluation $evaluation) : void {
        if($this->user->id == $evaluation->user_id) {
            $assigned_worker = AssignedWorker::findMe($evaluation->assigned_worker_id);
            if($assigned_worker->status == AssignedWorkerStatus::WORKING->value ||
                $assigned_worker->status == AssignedWorkerStatus::EVALUATION->value) {
                $evaluation->delete();
            }
            else throw HttpErrorsException::getInstance([__('errors.management.no_target')], 406);
        }
        else throw HttpException::getInstance(403);
    }
}
