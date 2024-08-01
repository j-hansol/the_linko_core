<?php

namespace App\Services\V1;

use App\DTOs\V1\OrderedTaskReportDto;
use App\DTOs\V1\OrderTaskDto;
use App\Http\QueryParams\ListQueryParam;
use App\Lib\MemberType;
use App\Lib\PageCollection;
use App\Models\OrderTask;
use App\Models\User;
use App\Services\Common\HttpException;
use Exception;
use Illuminate\Database\Eloquent\Builder;

class MonitoringService {
    private ?User $user = null;
    private ?User $manager = null;

    function __construct() {
        $this->user = current_user();
        $this->manager = $this->user?->getAffiliationManager();
    }

    /**
     * 서비스 프로바이더를 통해 인스턴스를 가져온다.
     * @return MonitoringService
     * @throws Exception
     */
    public static function getInstance() : MonitoringService {
        $instance = app(static::class);
        if(!$instance) throw new Exception('service not constructed');
        return $instance;
    }

    /**
     * 요청한 업무 목록을 리턴한다. $target에 따라 order_user_id, target_manager_user_id, target_user_id를 기준으로 목록을 추 ㅜㄹ한다.
     * @param ListQueryParam $param
     * @param string $target
     * @return PageCollection
     */
    private function _listOrderedTask(ListQueryParam $param, string $target) : PageCollection {
        $query = OrderTask::query()
            ->where($target, $this->user->id)
            ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                $query->where($param->field, $param->operator, $param->keyword);
            });
        $total = $query->count();
        $total_page = ceil($total / $param->page_per_items);
        $collection = $query->skip($param->start_rec_no)->take($param->page_per_items);
        return new PageCollection($total, $total_page, $collection);
    }

    /**
     * 관리기관 및 실무자가 요청한 업무 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     */
    public function listOrderedTask(ListQueryParam $param) : PageCollection {
        return $this->_listOrderedTask($param, 'order_user_id');
    }

    /**
     * 요청한 업무 정보를 리턴한다.
     * @param OrderTask $task
     * @return OrderTask
     * @throws HttpException
     */
    public function getOrderedTask(OrderTask $task) : OrderTask {
        if($this->user->id == $task->user_id || $this->user->id == $task->order_user_id ||
            $this->user->id == $task->manager_user_id) {
            return $task;
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 업무 요청 내용을 등록한다.
     * @param OrderTaskDto $dto
     * @return void
     * @throws HttpException
     */
    public function orderTask(OrderTaskDto $dto) : void {
        $manager = $this->user->getAffiliationManager();
        if($this->user?->isOwnType(MemberType::TYPE_MANAGER) ||
            ($this->user?->id == $manager?->id && $this->user?->isOwnType(MemberType::TYPE_MANAGER_OPERATOR))) {
            OrderTask::create([
                'order_user_id' => $this->user->id,
                'target_manager_user_id' => $this->manager->id
            ] + $dto->toArray());
        }
        else throw HttpException::getInstance(403);
    }

    /**
     * 실무자 본인에게 부여된 업무 요청정보 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     */
    public function listTaskOrdered(ListQueryParam $param) : PageCollection {
        return $this->_listOrderedTask($param, 'target_user_id');
    }


    public function addReport(OrderedTaskReportDto $dto, OrderTask $task) : void {

    }
}
