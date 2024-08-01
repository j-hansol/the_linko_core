<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class WorkerInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'skills', 'jobs', 'hobby', 'education_part', 'medical_support', 'height', 'weight', 'blood_type',
        'birth_place', 'civil_status', 'religion', 'language', 'region', 'current_address', 'spouse', 'children_names'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 해당 사용자의 정보를 리턴한다.
     * @param User $user
     * @return WorkerInfo|null
     */
    public static function findByUser(User $user) : ?WorkerInfo {
        return static::where('user_id', $user->id)->get()->first();
    }
}
