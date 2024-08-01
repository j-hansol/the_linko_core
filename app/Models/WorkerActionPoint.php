<?php

namespace App\Models;

use App\Lib\ActionPointType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerActionPoint extends Model {
    use HasFactory;

    protected $fillable = [
        'contract_id', 'assigned_worker_id', 'company_user_id', 'worker_id', 'author_user_id',
        'type', 'name', 'address', 'longitude', 'latitude', 'radius'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 지정 배정 근로자의 지정 활동 지점 정보를 리턴한다.
     * @param AssignedWorker $worker
     * @param ActionPointType $type
     * @return WorkerActionPoint|null
     */
    public static function getWorkerActionPoint(AssignedWorker $worker, ActionPointType $type) : ?WorkerActionPoint {
        return static::query()
            ->where('assigned_worker_id', $worker->id)
            ->where('type', $type->value)
            ->get()->first();
    }
}
