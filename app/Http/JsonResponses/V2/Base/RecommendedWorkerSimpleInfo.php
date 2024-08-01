<?php

namespace App\Http\JsonResponses\V2\Base;

use App\Lib\BodyType;
use App\Lib\ExcludeItem;
use App\Models\RecommendedWorker;
use App\Models\User;
use App\Models\WorkerBodyPhoto;
use App\Models\WorkerRecommendation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

class RecommendedWorkerSimpleInfo extends JsonResponse {
    function __construct(RecommendedWorker $worker, WorkerRecommendation $recommendation) {
        parent::__construct(static::toArray($worker, $recommendation));
    }

    /**
     * 간단한 추천 근로자 정보를 배열로 리턴한다.
     * @param RecommendedWorker $worker
     * @param WorkerRecommendation $recommendation
     * @return array
     * @OA\Schema(
     *     schema="recommended_worker_simple_info",
     *     title="간단한 추천 근로자 정보",
     *     @OA\Property(property="id", type="integer", description="일련번호"),
     *     @OA\Property(property="name", type="string", description="근로자 이름"),
     *     @OA\Property(property="gender", type="string", description="성별"),
     *     @OA\Property(property="age", type="integer", description="나이"),
     *     @OA\Property(property="face", type="string", description="얼굴사진 URL"),
     *     @OA\Property(property="status", type="integer", description="추천 근로자 상태")
     * )
     */
    public static function toArray(RecommendedWorker $worker, WorkerRecommendation $recommendation) : array {
        $user = User::findMe($worker->worker_user_id);
        $face = WorkerBodyPhoto::query()
            ->where('user_id', $user->id)
            ->where('type', BodyType::FACE->value)
            ->get()->first();
        $age = 0;

        if(in_array(ExcludeItem::PHOTO->name, json_decode($recommendation->excluded_informations))) $face = null;

        if($user->birthday) {
            $birthday = Carbon::createFromFormat('Y-m-d', $user->birthday);
            $age = $birthday->diffInYears(Carbon::now());
        }
        return [
            'id' => $worker->id,
            'name' => $user->name,
            'gender' => $user->sex,
            'age' => $age != 0 ? $age : null,
            'face' => $face ? route('api.v1.worker.worker_body_photo', ['id' => $face->id, '_token' => access_token()]) : null,
            'status' => $worker->status
        ];
    }
}
