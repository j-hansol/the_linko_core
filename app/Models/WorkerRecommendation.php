<?php

namespace App\Models;

use App\Traits\Common\FindMe;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerRecommendation extends Model {
    use HasFactory, FindMe;

    protected $fillable = [
        'worker_recommendation_request_id', 'user_id', 'provided_models', 'excluded_informations', 'expire_date', 'active'];

    /**
     * 공유대상 회원의 간략한 정보를 배열로 리턴한다.
     * @return array
     */
    public function getTargetUsers() : array {
        $ids = $this->hasMany(WorkerRecommendationTarget::class, 'worker_recommendation_id')->get()->pluck('user_id')->toArray();
        $targets = [];
        foreach($ids as $id) {
            $targets[] = User::findMe($id)->toSimpleArray();
        }

        return [
            'ids' => $ids,
            'users' => $targets
        ];
    }
}
