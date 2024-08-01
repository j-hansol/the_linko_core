<?php

namespace App\Http\Requests\V1;

use App\Lib\ContractType;
use App\Lib\MemberType;
use App\Rules\OwnType;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestAddContract extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 계약 등록 또는 수정을 위한 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="add_contract",
     *     title="계약 추가",
     *     @OA\Property(
     *          property="recipient_user_id",
     *          type="integer",
     *          description="수주자 계정 일련번호, 계약 유형이 직접인 경우 필수"
     *     ),
     *     @OA\Property(
     *          property="occupational_group_id",
     *          type="integer",
     *          description="계약 대상 직업군 일련번호"
     *     ),
     *     @OA\Property(
     *          property="title",
     *          type="string",
     *          description="계약 제목"
     *     ),
     *     @OA\Property(
     *          property="body",
     *          type="string",
     *          description="계약 내용"
     *     ),
     *     @OA\Property(
     *          property="type",
     *          ref="#/components/schemas/ContractType"
     *     ),
     *     @OA\Property(
     *          property="worker_count",
     *          type="integer",
     *          description="채용 계약 근로자 수"
     *     ),
     *     @OA\Property(
     *          property="contract_date",
     *          type="string",
     *          format="date",
     *          description="계약일자"
     *     ),
     *     required={"occupational_group_id", "title", "body", "type", "worker_count"}
     * )
     */
    public function rules(): array {
        $type = $this->enum('type', ContractType::class);
        $condition = $type == ContractType::INTERMEDIARY ? 'nullable' : 'required';
        return [
            'recipient_user_id' => [$condition, 'integer', 'exists:users,id', new OwnType(MemberType::TYPE_RECIPIENT)],
            'occupational_group_id' => ['required', 'integer', 'exists:occupational_groups,id'],
            'title' => ['required', 'string'],
            'body' => ['required', 'string'],
            'type' => ['required', 'integer', new Enum(ContractType::class)],
            'worker_count' => ['required', 'integer', 'min:1'],
            'contract_date' => ['nullable', 'date', 'date_format:Y-m-d'],
        ];
    }
}
