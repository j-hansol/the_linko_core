<?php

namespace App\Http\Requests\V2;

use App\Lib\ExcludeItem;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class RequestSaveWorkerRecommendationForOperator extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     */
    public function rules(): array {
        $model = config('worker_recommendation.model_alias');

        return [
            'occupational_group_id' => ['required', 'integer', 'exists:occupational_groups,id'],
            'title' => ['required'],
            'body' => ['required'],
            'worker_count' => ['required', 'integer', 'min:1'],
            'provided_models.*' => ['nullable', 'string', Rule::in($model)],
            'excluded_informations' => ['nullable', new Enum(ExcludeItem::class)],
            'expire_date' => ['required', 'date', 'date_format:Y-m-d'],
            'target_user_ids' => ['nullable', 'integer', 'exists:users,id'],
            'active' => ['required', 'boolean']
        ];
    }
}
