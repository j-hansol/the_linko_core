<?php

namespace App\Services\V1;

use App\DTOs\V1\EvalInfoDto;
use App\DTOs\V1\EvalItemDto;
use App\DTOs\V1\OccupationalGroupDto;
use App\DTOs\V1\VisaDocumentTypeDto;
use App\Http\QueryParams\ListQueryParam;
use App\Lib\MemberType;
use App\Lib\PageCollection;
use App\Models\AccessToken;
use App\Models\EvalInfo;
use App\Models\EvalItem;
use App\Models\OccupationalGroup;
use App\Models\User;
use App\Models\UserType;
use App\Models\VisaDocument;
use App\Models\VisaDocumentType;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OperatorService {
    protected ?User $user;

    function __construct() {$this->user = current_user();}
    /**
     * 서비스 프로바이더를 통해 인스턴스를 가져온다.
     * @return OperatorService
     * @throws Exception
     */
    public static function getInstance() : OperatorService {
        $instance = app(static::class);
        if(!$instance) throw new Exception('service not constructed');
        return $instance;
    }

    /**
     * 관리자가 다른 회원 계정으로 전환한다.
     * @param User $user
     * @return void
     * @throws HttpErrorsException
     */
    public function switchUser(User $user) : void {
        if($this->user && !$this->user->isSwitchedUser()) {
            $token = AccessToken::find(access_token());
            $token->setSwitchUser($user);
        }
        else throw HttpErrorsException::getInstance([__('errors.operator.only_operator')], 406);
    }

    /**
     * 계정 전환을 해제한다.
     * @return void
     * @throws HttpErrorsException
     */
    public function exitSwitchedUser() : void {
        if($this->user && $this->user->isSwitchedUser()) {
            $token = AccessToken::find(access_token());
            $token->resetSwitchedUser();
        }
        else throw HttpErrorsException::getInstance([__('errors.operator.only_operator')], 406);
    }

    /**
     * 직업군 정보를 갱신한다.
     * @param OccupationalGroupDto $dto
     * @param OccupationalGroup $group
     * @return void
     * @throws HttpException
     */
    public function updateOccupationalGroup(OccupationalGroupDto $dto, OccupationalGroup $group) : void {
        if($this->user) {
            $group->fill($dto->toArray());
            $group->save();
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 회원 유형별 목록을 리턴한다.
     * @param ListQueryParam $param
     * @param MemberType $type
     * @return Collection
     */
    private function _listByUserType(ListQueryParam $param, MemberType $type) : Collection {
        return User::query()
            ->select('users.*')
            ->join('user_types', 'users.id', '=', 'user_types.user_id')
            ->orderBy($param->order, $param->direction)
            ->where('user_types.type', $type->value)
            ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                $query->where($param->field, $param->operator, $param->keyword);
            })
            ->skip($param->start_rec_no)->take($param->page_per_items)
            ->get();
    }

    /**
     * 회원 유형별 목록을 리턴한다.
     * @param ListQueryParam $param
     * @param MemberType $type
     * @return PageCollection
     * @throws HttpException
     */
    public function listByMemberType(ListQueryParam $param, MemberType $type) : PageCollection {
        if($this->user) {
            $query = User::query()
                ->select('users.*')
                ->join('user_types', 'users.id', '=', 'user_types.user_id')
                ->orderBy($param->order, $param->direction)
                ->where('user_types.type', $type->value)
                ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
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
     * 개정 활성화 여부를 설정한다.
     * @param bool $activate
     * @param User $user
     * @return void
     * @throws HttpException
     */
    public function updateActivate(bool $activate, User $user) : void {
        if($this->user) {
            $user->active = $activate ? 1 : 0;
            $user->save();
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 지정 사용자에게 지정 회원 유형들을 적용 가능한지 판단한다.
     * @param array $types
     * @param User $user
     * @return bool
     */
    private function _isAvailableUserType(array $types, User $user) : bool {
        $target_type = [];
        foreach($types as $value) {
            if( $value instanceof  MemberType) {$target_type[] = $value; continue;}
            elseif(!is_null($t = MemberType::tryFrom($value))) {$target_type[] = $t;continue;}
            else return false;
        }

        if($user->is_organization == 1) {
            foreach ($target_type as $type)
                if (!$type->checkOrganization()) return false;
        }
        else {
            foreach($target_type as $type)
                if(!$type->checkPerson()) return false;
        }

        return true;
    }

    /**
     * 지정 계정의 회원 유형을 변경한다.
     * @param array $types
     * @param User $user
     * @return void
     * @throws HttpErrorsException
     */
    public function updateUserType(array $types, User $user) : void {
        if($this->user && $this->_isAvailableUserType($types, $user)) UserType::sync($user, $types);
        else throw HttpErrorsException::getInstance([__('errors.user.invalid_types')], 400);
    }

    /**
     * 비자발급시 필요한 문서 유형을 등록한다.
     * @param VisaDocumentTypeDto $dto
     * @return void
     * @throws HttpException
     */
    public function addVisaDocumentType(VisaDocumentTypeDto $dto) : void {
        if($this->user) VisaDocumentType::create($dto->toArray());
        else throw HttpException::getInstance(401);
    }

    /**
     * 비자발급시 필요한 문서 유형을 갱신한다.
     * @param VisaDocumentTypeDto $dto
     * @param VisaDocumentType $type
     * @return void
     * @throws HttpException
     */
    public function updateVisaDocumentType(VisaDocumentTypeDto $dto, VisaDocumentType $type) : void {
        if($this->user) {
            $type->fill($dto->toArray());
            $type->save();
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 비자발급시 필요한 문서 유형을 삭제한다.
     * @param VisaDocumentType $type
     * @return void
     * @throws HttpException|HttpErrorsException
     */
    public function deleteVisaDocumentType(VisaDocumentType $type) : void {
        if($this->user) {
            if(VisaDocument::countDocumentByType($type) > 0)
                throw HttpErrorsException::getInstance([__('errors.visa.no_delete_able')], 406);
            $type->delete();
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 평가 마스터 정보를 등록한다.
     * @param EvalInfoDto $dto
     * @return void
     * @throws HttpException
     */
    public function addEvalInfo(EvalInfoDto $dto) : void {
        if($this->user) EvalInfo::create($dto->toArray());
        else throw HttpException::getInstance(401);
    }

    /**
     * 평가 마스터 정보를 변경한다.
     * @param EvalInfoDto $dto
     * @param EvalInfo $info
     * @return void
     * @throws HttpException
     */
    public function updateEvalInfo(EvalInfoDto $dto, EvalInfo $info) : void {
        if($this->user) {
            $info->fill($dto->toArray());
            $info->save();
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 평가 마스터 정보를 삭제한다.
     * @param EvalInfo $info
     * @return void
     * @throws HttpException
     */
    public function deleteEvalInfo(EvalInfo $info) : void {
        if($this->user) $info->delete();
        else throw HttpException::getInstance(401);
    }

    /**
     * 평가 항목을 등록한다.
     * @param EvalItemDto $dto
     * @param EvalInfo $info
     * @return void
     * @throws HttpException
     */
    public function addEvalItem(EvalItemDto $dto, EvalInfo $info) : void {
        if($this->user) {
            $ids = ['eval_info_id' => $info->id];
            EvalItem::create($ids + $dto->toArray());
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 지정 평가 항목을 수정한다.
     * @param EvalItemDto $dto
     * @param EvalItem $item
     * @return void
     * @throws HttpException
     */
    public function updateEvalItem(EvalItemDto $dto, EvalItem $item) : void {
        if($this->user) {
            $item->fill($dto->toArray());
            $item->save();
        }
        else throw HttpException::getInstance(401);
    }

    /**
     * 지정 평가 항목을 삭제한다.
     * @param EvalItem $item
     * @return void
     * @throws HttpException
     */
    public function deleteEvalItem(EvalItem $item) : void {
        if($this->user) $item->delete();
        else throw HttpException::getInstance(401);
    }
}
