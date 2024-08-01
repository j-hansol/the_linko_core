<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerActionPointHistory extends Model {
    protected $fillable = [
        'contract_id', 'assigned_worker_id', 'company_user_id', 'worker_id', 'author_user_id',
        'type', 'name', 'address', 'longitude', 'latitude', 'radius'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 기존 근로자 활동지점 정보를 내역에 저장한다.
     * @param WorkerActionPoint $point
     * @return WorkerActionPointHistory|null
     */
    public static function createFrom(WorkerActionPoint $point) : ?WorkerActionPointHistory {
        return static::create([
            'source_id' => $point->id,
            'contract_id' => $point->contract_id,
            'assigned_worker_id' => $point->assigned_worker_id,
            'company_user_id' => $point->company_user_id,
            'worker_id' => $point->worker_id,
            'author_user_id' => $point->author_user_id,
            'type' => $point->type,
            'name' => $point->name,
            'address' => $point->address,
            'longitude' => $point->longitude,
            'latitude' => $point->latitude,
            'radius' => $point->radius
        ]);
    }
}
