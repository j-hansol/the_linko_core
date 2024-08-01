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
use OpenApi\Annotations as OA;

class RequestWorkerRecommendation extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="input_worker_recommendation",
     *     title="근로자 추천 정보 입력",
     *     @OA\Property(property="provided_models", type="array", description="공유 대상 정보 데이터 모델 별칭", @OA\Items(type="string")),
     *     @OA\Property(property="exclude_items", type="array", description="제외 대상 정보", @OA\Items(type="integer", ref="#/components/schemas/ExcludeItem")),
     *     @OA\Property(property="expire_date", type="string", format="date-time", description="만료일시"),
     *     @OA\Property(property="target_user_ids", type="array", description="추천 대상 계정 일련번호 목록", @OA\Items(type="integer")),
     *     @OA\Property(property="target_user_id_aliases", type="array", description="추천 대상 계정 별칭 목록", @OA\Items(type="string")),
     *     @OA\Property(property="active", type="integer", enum={"0", "1"}, description="사용 어부 설정"),
     *     required={"provided_models", "active"}
     * )
     */
    public function rules(): array {
        return [
            'provided_models' => ['required', new InArrayValues(config('worker_recommendation.model_alias'))],
            'exclude_items' => ['nullable', new InEnumValues(ExcludeItem::class)],
            'expire_date' => ['required', 'date', 'date_format:Y-m-d'],
            'target_user_ids' => ['nullable', new ExistsValues('users', 'id')],
            'target_user_id_aliases' => [(new RequiredOrNull())->required(empty($this->input('target_user_ids'))), new ExistsValues('users', 'id_alias')],
            'active' => ['required', 'boolean']
        ];
    }
}
