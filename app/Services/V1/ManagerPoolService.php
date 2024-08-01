<?php

namespace App\Services\V1;

use App\Http\QueryParams\ListQueryParam;
use App\Lib\MemberType;
use App\Lib\PageCollection;
use App\Models\User;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ManagerPoolService {
    protected ?User $order;

    function __construct() {$this->order = current_user();}

    /**
     * 서비스 프로바이더를 통해 인스턴스를 가져온다.
     * @return ManagerPoolService
     * @throws Exception
     */
    public static function getInstance() : ManagerPoolService {
        $instance = app(static::class);
        if($instance) throw new Exception('service not found');
        return $instance;
    }

    /**
     * 접속 회원의 유형에 따라 지정 가능한 관리기관 회원 유형을 리턴한다.
     * @return MemberType|null
     */
    private function getManagerType() : ?MemberType {
        if($this->order->isOwnType(MemberType::TYPE_ORDER)) return MemberType::TYPE_MANAGER;
        elseif($this->order->isOwnType(MemberType::TYPE_RECIPIENT)) return MemberType::TYPE_FOREIGN_MANAGER;
        else return null;
    }

    /**
     * 소속 관리기관 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     */
    public function listManager(ListQueryParam $param) : PageCollection {
        $query = User::query()
            ->select('users.*')
            ->join('managers', 'users.id', '=', 'managers.manager_user_id')
            ->orderBy($param->order, $param->direction)
            ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                $query->where($param->field, $param->operator, $param->keyword);
            })
            ->where('managers.organization_user_id', $this->order->id);
        $total = $query->count();
        $total_page = ceil($total / $param->page_per_items);
        $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
            ->get();
        return new PageCollection($total, $total_page, $collection);
    }

    /**
     * 지정 가능한 관리기관 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     */
    public function listAbleManager(ListQueryParam $param) : PageCollection {
        $query = User::query()
            ->select('users.*')
            ->join('user_types', 'users.id', '=', 'user_types.user_id')
            ->orderBy($param->order, $param->direction)
            ->where('user_types.type', $this->getManagerType())
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
     * 지정 단체 회원을 소속 관리기관으로 추가한다.
     * @param User $manager
     * @return void
     * @throws HttpErrorsException
     */
    public function add(User $manager) : void {
        if($manager->isOwnType($this->getManagerType())) {
            DB::table('managers')
                ->insert([
                    'organization_user_id' => $this->order->id,
                    'manager_user_id' => $manager->id
                ]);
        }
        else throw HttpErrorsException::getInstance([__('errors.management.no_target')], 406);
    }

    /**
     * 지정 관리기관을 소속 관리기관에서 제외한다.
     * @param User $manager
     * @return void
     * @throws HttpErrorsException
     */
    public function delete(User $manager) : void {
        if ($manager->isOwnType($this->getManagerType())) {
            DB::table('managers')
                ->where('organization_user_id', $this->order->id)
                ->where('manager_user_id', $manager->id)
                ->delete();
        }
        else throw HttpErrorsException::getInstance([__('errors.management.no_target')], 406);
    }
}
