<?php

namespace App\Http\JsonResponses\V2\Base;

use App\Models\User;
use App\Models\WorkerRecommendation;
use App\Models\WorkerRecommendationRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class WorkerRecommendationInfo extends JsonResponse {
    function __construct(WorkerRecommendation $recommendation, bool $show_request = false) {
        parent::__construct(static::toArray($recommendation, $show_request));
    }

    /**
     * 공유정보를 배열로 리턴한다.
     * @param WorkerRecommendation $recommendation
     * @return array
     * @OA\Schema(
     *     schema="worker_recommendation_info",
     *     title="근로자 추천 정보",
     *     @OA\Property(property="id", type="integer", description="일련번호"),
     *     @OA\Property(property="worker_recommendation_request", ref="#/components/schemas/worker_recommendation_request_info"),
     *     @OA\Property(property="worker_recommendation_request_id", type="integer", description="요청정보 일련번호"),
     *     @OA\Property(property="user", ref="#/components/schemas/simple_user_info"),
     *     @OA\Property(property="target_users", type="array", description="공유 대상 계정 일련번호", @OA\Items(ref="#/components/schemas/simple_user_info")),
     *     @OA\Property(property="provided_models", type="array", description="제공 정보 데이터 목록", @OA\Items(type="string")),
     *     @OA\Property(property="exclude_items", type="array", description="제외 항목 목록", @OA\Items(type="string")),
     *     @OA\Property(property="expire_date", type="string", format="date", description="만룡일자"),
     *     @OA\Property(property="active", type="integer", description="사용 여부"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="등록일시"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", description="변경일시")
     * )
     */
    public static function toArray(WorkerRecommendation $recommendation, bool $show_request = false) : array {
        $target_users = $recommendation->getTargetUsers();
        $request = WorkerRecommendationRequest::findMe($recommendation->worker_recommendation_request_id);
        return [
            'id' => $recommendation->id,
            'worker_recommendation_request' => $show_request ? WorkerRecommendationRequestInfo::toArray($request) : null,
            'worker_recommendation_request_id' => $recommendation->worker_recommendation_request_id,
            'user' => User::findMe($recommendation->user_id)->toSimpleArray(),
            'target_users' => $target_users['users'],
            'provided_models' => static::_getModelAlias(json_decode($recommendation->provided_models)),
            'exclude_items' => json_decode($recommendation->excluded_informations),
            'expire_date' => $recommendation->expire_date,
            'active' => $recommendation->active,
            'created_at' => $recommendation->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $recommendation->updated_at->format('Y-m-d H:i:s')
        ];
    }

    /**
     * 모델 별칭을 배열로 리턴한다.
     * @param array|null $models
     * @return array|null
     */
    private static function _getModelAlias(?array $models) : ?array {
        $model_alias = config('worker_recommendation.model_alias');
        $alias = [];
        if(!$models) return $alias;
        foreach($models as $model) $alias[] = $model_alias[$model];
        return $alias;
    }
}
