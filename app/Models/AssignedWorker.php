<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class AssignedWorker extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id', 'company_user_id', 'worker_user_id', 'manager_operator_user_id', 'attorney_user_id',
        'entry_schedule_id', 'status'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 근로자 계정 정보를 배열로 리턴한다.
     * @return array
     */
    public function toWorkerInfoArray() : array {
        return User::findMe($this->worker_user_id)->toInfoArray();
    }

    /**
     * 지정 ID의 정보를 리턴한다.
     * @param int|null $id
     * @return AssignedWorker|null
     */
    public static function findMe(?int $id) : ?AssignedWorker {
        if(!$id) return null;
        return static::find($id);
    }

    /**
     * 배정 근로자 정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="assigned_worker",
     *     title="배정 근로자 정보",
     *     @OA\Property (property="id", type="integer", description="일련번호"),
     *     @OA\Property (property="contract", ref="#/components/schemas/simple_contract_data"),
     *     @OA\Property (property="company", ref="#/components/schemas/simple_user_info"),
     *     @OA\Property (property="worker", ref="#/components/schemas/simple_user_info"),
     *     @OA\Property (property="entry_schedule", ref="#/components/schemas/entry_info_data"),
     *     @OA\Property (property="created_at", type="string", type="date-time", description="생성일시"),
     *     @OA\Property (property="updated_at", type="string", type="date-time", description="수정일시")
     * )
     */
    public function toInfoArray() : array {
        return [
            'id' => $this->id,
            'contract' => Contract::findMe($this->contract_id)?->toSimpleInfoArray(),
            'company' => User::findMe($this->company_user_id)?->toSimpleArray(),
            'worker' => User::findMe($this->worker_user_id)?->toWorkerInfoArray(),
            'entry_schedule' => EntrySchedule::findMe($this->entry_schedule_id)?->toInfoArray(),
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
