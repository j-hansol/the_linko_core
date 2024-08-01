<?php

namespace App\Http\JsonResponses\V2\Base;

use App\Models\OccupationalGroup;
use App\Models\User;
use App\Models\WorkerRecommendationRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class WorkerRecommendationRequestInfo extends JsonResponse {
    function __construct(WorkerRecommendationRequest $request) {
        parent::__construct(static::toArray($request));
    }

    /**
     * 공유 요청 정보를 배열로 리턴한다.
     * @param WorkerRecommendationRequest $request
     * @return array
     * @OA\Schema(
     *     schema="worker_recommendation_request_info",
     *     title="근로자 추천 요청 정보",
     *     @OA\Property(property="id", type="integer", description="일련번호"),
     *     @OA\Property(property="user", ref="#/components/schemas/simple_user_info"),
     *     @OA\Property(property="occupational_group", ref="#/components/schemas/occupational_group"),
     *     @OA\Property(property="title", type="string", description="추천 요청 제목"),
     *     @OA\Property(property="body", type="string", description="추천 요청 내용"),
     *     @OA\Property(property="status", type="integer", description="요청 처리 상태"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="등록일시"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", description="변경일시")
     * )
     */
    public static function toArray(WorkerRecommendationRequest $request) : array {
        return [
            'id' => $request->id,
            'user' => User::findMe($request->user_id)->toSimpleArray(),
            'occupational_group' => OccupationalGroup::findMe($request->occupational_group_id)->toInfoArray(),
            'title' => $request->title,
            'body' => $request->body,
            'worker_count' => $request->worker_count,
            'status' => $request->status,
            'created_at' => $request->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $request->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
