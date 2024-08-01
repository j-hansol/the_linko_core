<?php

namespace App\Http\Requests\V2;

use App\Lib\ExcludeItem;
use App\Rules\ExistsValues;
use App\Rules\InArrayValues;
use App\Rules\InEnumValues;
use App\Rules\RequiredOrNull;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\RequiredIf;
use OpenApi\Annotations as OA;

class RequestWorkerRecommendationRequestForOperator extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="input_worker_recommendation_request_for_operator",
     *     title="운영자를 위한 근로자 추천 요청 및 추천 정보 입력",
     *     @OA\Property(property="target_user_ids", type="array", description="추천 대상 계정 일련번호 목록", @OA\Items(type="integer")),
     *     @OA\Property(property="target_user_id_aliases", type="array", description="추천 대상 계정 별칭 목록", @OA\Items(type="string")),
     *     @OA\Property(property="worker_count", type="integer", description="추천 근로자 수"),
     *     @OA\Property(property="occupational_group_id", type="integer", description="직업군 일련번호"),
     *     @OA\Property(property="title", type="string", description="근로자 추쳔 요청 제목"),
     *     @OA\Property(property="body", type="string", description="근로자 추천 요청 내용"),
     *     @OA\Property(property="provided_models", type="array", description="공유 대상 정보 데이터 모델 별칭", @OA\Items(type="string")),
     *     @OA\Property(property="exclude_items", type="array", description="제외 대상 정보", @OA\Items(type="integer", ref="#/components/schemas/ExcludeItem")),
     *     @OA\Property(property="expire_date", type="string", format="date-time", description="만료일시"),
     *     @OA\Property(property="active", type="integer", enum={"0", "1"}, description="사용 어부 설정"),
     *     required={"worker_count", "occupational_group_id", "title", "body", "provided_models", "expire_date", "active"}
     * )
     */
    public function rules(): array {
        return [
            'target_user_ids' => ['nullable', new ExistsValues('users', 'id')],
            'target_user_id_aliases' => [(new RequiredOrNull())->required(empty($this->input('target_user_ids'))), new ExistsValues('users', 'id_alias')],
            'worker_count' => ['nullable', 'integer'],
            'occupational_group_id' => ['required', 'integer', 'exists:occupational_groups,id'],
            'title' => ['required'],
            'body' => ['required'],
            'provided_models' => ['required', new InArrayValues(config('worker_recommendation.model_alias'))],
            'exclude_items' => ['nullable', new InEnumValues(ExcludeItem::class)],
            'expire_date' => ['required', 'date', 'date_format:Y-m-d'],
            'active' => ['required', 'boolean']
        ];
    }
}
