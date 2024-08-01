<?php
namespace App\Services\V2;

use App\Http\QueryParams\CountryParam;
use App\Http\QueryParams\ListQueryParam;
use App\Lib\MemberType;
use App\Lib\PageCollection;
use App\Models\PreSaveWorkerInfo;
use App\Models\User;
use App\Services\Common\HttpException;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;

class WorkerManagementService {
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
    public function listWorkerForOperator(ListQueryParam $param) : PageCollection {
        $query = User::query()
            ->orderBy($param->order, $param->direction)
            ->select('users.*')
            ->join('user_types', 'users.id', '=', 'user_types.user_id')
            ->where('is_organization', 0)
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
}
