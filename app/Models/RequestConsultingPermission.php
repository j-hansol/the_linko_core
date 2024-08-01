<?php

namespace App\Models;

use App\Events\ConsultingConfirmed;
use App\Lib\RequestConsultingPermissionStatus;
use App\Lib\VisaApplicationStatus;
use App\Notifications\ConsultingAttorneyAssigned;
use App\Notifications\ConsultingPermissionConfirmed;
use App\Notifications\ConsultingPermissionRejected;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class RequestConsultingPermission extends Model {
    use HasFactory;

    protected $fillable = ['visa_application_id', 'user_id', 'max_datetime', 'status'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 지정 비자발급 정보에 컨설팅 행정사가 배정되었는지 여부를 판단한다.
     * @param VisaApplication $visa
     * @return bool
     */
    public static function isAssigned(VisaApplication $visa) : bool {
        return $visa->consulting_user_id != null;
    }

    /**
     * 지정 비자발급 정보에 대해 컨설팅 권한 요청을 했는지 여부를 판단한다.
     * @param VisaApplication $visa
     * @param User $attorney
     * @return bool
     */
    public static function isRequested(VisaApplication $visa, User $attorney) : bool {
        $request_info = static::where('visa_application_id', $visa->id)
            ->where('user_id', $attorney->id)->get()->first();
        return $request_info != null;
    }

    /**
     * 비자발급 컨설팅 권한을 수락한다.
     * @param VisaApplication $visa
     * @param User $attorney
     * @return bool
     */
    private static function _setConfirmed(VisaApplication $visa, User $attorney) : bool {
        DB::beginTransaction();
        try {
            $visa->consulting_user_id = $attorney->id;
            $visa->status = VisaApplicationStatus::STATUS_START_PREVIEW->value;
            $visa->save();

            static::where('visa_application_id', $visa->id)
                ->where('user_id', $attorney->id)
                ->update(['status' => RequestConsultingPermissionStatus::CONFIRMED->value]);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * 권한 요청 정보를 리턴한다.
     * @param VisaApplication $visa
     * @return Collection
     */
    public static function getRequestedInfo(VisaApplication $visa) : Collection {
        return static::where('visa_application_id', $visa->id)
            ->where('status', RequestConsultingPermissionStatus::REQUESTED->value)
            ->get();
    }

    /**
     * 권한 요청을 한 행정사의 간단한 정보를 배열로 리턴한다.
     * @param VisaApplication $visa
     * @return array|null
     */
    public static function listRequestedAttorney(VisaApplication $visa) : ?array {
        $user_ids = static::getRequestedInfo($visa)->pluck('user_id')->toArray();
        $users = User::find($user_ids);
        if($users->isNotEmpty()) {
            $ret = [];
            foreach($users as $user) $ret[] = $user->toSimpleArray();
            return $ret;
        } else return null;
    }

    /**
     * 지장 비자발급 정보에 대한 컨설팅 권한 요청을 반려하고 통보한다.
     * @param VisaApplication $visa
     * @param User|null $excluded
     * @return void
     */
    private static function _setRejected(VisaApplication $visa, ?User $excluded) : void {
        $list = static::getRequestedInfo($visa);
        if($list->isNotEmpty()) {
            foreach($list as $info) {
                $attorney = User::findMe($info->user_id);
                $info->status = RequestConsultingPermissionStatus::REJECTED->value;
                $info->save();
                $attorney->notify(new ConsultingPermissionRejected());
            }
        }
    }

    /**
     * 신청된 비자발갑 정보의 컨설팅 행정사를 지정 또는 수락한다.
     * @param VisaApplication $visa
     * @param User $attorney
     * @return bool
     */
    public static function setConfirmed(VisaApplication $visa, User $attorney) : bool {
        if(static::_setConfirmed($visa, $attorney)) {
            static::_setRejected($visa, $attorney);
            ConsultingConfirmed::dispatch($visa);
            return true;
        } else return false;
    }

    /**
     * 다수의 비자발급 신청 정보에 대한 컨설팅 행정사를 지정한다.
     * @param array $ids
     * @param User $attorney
     * @return bool
     */
    public static function setConfirmedMultiple(array $ids, User $attorney) : bool {
        $visa_infos = VisaApplication::find($ids);
        $confirmed_count = 0;
        foreach($visa_infos as $visa) {
            $worker = User::findMe($visa->user_id);
            if(static::_setConfirmed($visa, $attorney)) {
                static::_setRejected($visa, $attorney);
                $confirmed_count++;
            }
        }

        if($confirmed_count > 0) {
            ConsultingConfirmed::dispatch($visa_infos);
            return true;
        }
        else return false;
    }

    /**
     * 비자발급정보와 행정사 정보를 받아 권한요청 정보를 생성한다.
     * @param VisaApplication $visa
     * @param User $attorney
     * @return void
     */
    public static function createRequestPermission(VisaApplication $visa, User $attorney) : void {
        $now = Carbon::now(config('app.timezone'));
        $now->addHours(env('MAX_REQUEST_CONSULTING_PERMISSION_TIME', 48));
        static::create([
            'visa_application_id' => $visa->id,
            'user_id' => $attorney->id,
            'max_datetime' => $now->format('Y-m-d H:i:s'),
            'status' => RequestConsultingPermissionStatus::REQUESTED
        ]);
    }

    /**
     * 컨설팅 권한 요청 정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="request_consulting_permission",
     *     title="컨설팅 권한 요청정보",
     *     @OA\Property(property="id",type="integer",description="일련번호"),
     *     @OA\Property(property="user",type="object",ref="#/components/schemas/simple_user_info",description="일련번호"),
     *     @OA\Property(property="max_datetime",type="date-time",description="최대처리 일시"),
     *     @OA\Property(property="status",type="integer",description="처리상태"),
     *     @OA\Property(property="created_at",type="date-time",description="생성일시"),
     *     @OA\Property(property="updated_at",type="date-time",description="수정일시"),
     * )
     */
    public function toInfoArray() : array {
        $arr = $this->toArray();
        $user = User::findMe($this->user_id);
        unset($arr['visa_application_id']);
        unset($arr['user_id']);
        $arr['user'] = $user?->toSimpleArray();
        return $arr;
    }
}
