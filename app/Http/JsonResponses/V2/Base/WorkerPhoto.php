<?php

namespace App\Http\JsonResponses\V2\Base;

use App\Models\User;
use App\Models\WorkerBodyPhoto;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class WorkerPhoto extends JsonResponse {
    function __construct(WorkerBodyPhoto $photo) {
        parent::__construct(static::toArray($photo));
    }

    /**
     * 근로자 사진정보를 배열로 리턴한다.
     * @param WorkerBodyPhoto $photo
     * @return array
     * @OA\Schema(
     *     schema="worker_body_photo",
     *     title="근로자 신체 사진정보",
     *     @OA\Property(property="id", type="integer", description="일련번호"),
     *     @OA\Property(property="user", ref="#/components/schemas/simple_user_info"),
     *     @OA\Property(property="type", type="integer", description="사진 유형"),
     *     @OA\Property(property="origin_name", type="string", description="원본 파일명"),
     *     @OA\Property(property="file_url", type="string", description="파일 URL")
     * )
     */
    public static function toArray(WorkerBodyPhoto $photo) : array {
        return [
            'id' => $photo->id,
            'user' => User::findMe($photo->user_id)->toSimpleArray(),
            'type' => $photo->type,
            'origin_name' => $photo->origin_name,
            'file_url' => route('api.v1.worker.show_body_photo', ['id' => $photo->id, '_token' => access_token()])
        ];
    }
}
