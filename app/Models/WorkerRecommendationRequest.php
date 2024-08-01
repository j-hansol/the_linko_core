<?php

namespace App\Models;

use App\Traits\Common\FindMe;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerRecommendationRequest extends Model {
    use HasFactory, FindMe;

    protected $fillable = ['user_id', 'occupational_group_id', 'title', 'body', 'worker_count', 'status'];

    /**
     * 승인된 추천정보를 리턴한다.
     * @return WorkerRecommendation|null
     */
    public function getRecommendation() : ?WorkerRecommendation {
        return $this->hasOne(WorkerRecommendation::class, 'worker_recommendation_request_id')->get()->first();
    }
}
