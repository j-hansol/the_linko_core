<?php

namespace App\Services\V1;

use App\DTOs\V1\ManagerOperatorDto;
use App\Http\QueryParams\ListQueryParam;
use App\Lib\LoginMethod;
use App\Lib\MemberType;
use App\Lib\PageCollection;
use App\Models\Country;
use App\Models\Device;
use App\Models\PasswordHistory;
use App\Models\User;
use App\Models\UserType;
use App\Services\Common\HttpException;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ManagerOperatorService {
    protected ?User $user;
    protected ?User $manager;

    function __construct() {
        $this->user = current_user();
        $this->manager = $this->user->getAffiliationManager();
    }

    /**
     * 서비스 프로바이더를 통해 인스턴스를 가져온다.
     * @return ManagerOperatorService
     * @throws Exception
     */
    public static function getInstance() : ManagerOperatorService {
        $instance = app(static::class);
        if(!$instance) throw new Exception('service not constructed');
        return $instance;
    }

    /**
     * 관리 실무자 계정 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listOperator(ListQueryParam $param) : PageCollection {
        $query = User::orderBy($param->order, $param->direction)
            ->select('users.*')
            ->join('user_types', 'users.id', '=', 'user_types.user_id')
            ->where('is_organization', 0)
            ->where('management_org_id', $this->manager->id)
            ->where('user_types.type', $this->manager->isOwnType(MemberType::TYPE_FOREIGN_MANAGER) ?
                MemberType::TYPE_FOREIGN_MANAGER_OPERATOR->value : MemberType::TYPE_MANAGER_OPERATOR->value)
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
     * 소속 관리 실무자 계정을 등록한다.
     * @param ManagerOperatorDto $dto
     * @return void
     * @throws HttpException|Exception
     */
    public function joinOperator(ManagerOperatorDto $dto) : void {
        if($this->manager) {
            $initial_user_type = $this->manager->isOwnType(MemberType::TYPE_FOREIGN_MANAGER)
                ? MemberType::TYPE_FOREIGN_MANAGER_OPERATOR: MemberType::TYPE_MANAGER_OPERATOR;
            $id_info = User::genInitialTemporaryIdAlias($initial_user_type->value);
            $country = Country::findMe($this->manager->country_id);
            $dto->setCountry($country);
            $dto->setName();
            $t = [
                'is_organization' => 0,
                'management_org_id' => $this->manager->id,
                'api_token' => User::genApiToken(),
                'login_method' => LoginMethod::LOGIN_METHOD_PASSWORD->value,
                'active' => 1
            ];
            $user = User::create($id_info + $dto->toArray() + $t);
            if($user instanceof User) {
                PasswordHistory::createByUser($user, $dto->getHashedPassword());
                UserType::createType($user, $initial_user_type);
                Device::createFixedDevice($user);
            }
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 소속 실무자 계정을 활성화한다.
     * @param User $operator
     * @param bool $active
     * @return void
     * @throws HttpException
     */
    public function setActiveOperator(User $operator, bool $active) : void {
        if($this->manager && $operator->management_org_id == $this->manager->id) {
            $operator->active = $active ? 1 : 0;
            $operator->save();
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 소속 실무자를 실무자 그룹에서 제외한다.
     * @param User $operator
     * @return void
     * @throws HttpException|Exception
     */
    public function cancelOperator(User $operator) : void {
        if ($this->manager && $operator->management_org_id == $this->manager->id) {
            DB::beginTransaction();
            try {
                $type = $this->manager->isOwnType(MemberType::TYPE_FOREIGN_MANAGER)
                    ? MemberType::TYPE_FOREIGN_MANAGER_OPERATOR : MemberType::TYPE_MANAGER_OPERATOR;
                $operator->management_org_id = null;
                $operator->save();
                UserType::removeType($operator, $type);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
        else throw HttpException::getInstance(401);
    }
}
