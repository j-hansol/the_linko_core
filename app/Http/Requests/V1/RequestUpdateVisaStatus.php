<?php

namespace App\Http\Requests\V1;

use App\Lib\VisaApplicationStatus;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestUpdateVisaStatus extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * @return array
     * @OA\Schema (
     *     schema="input_visa_issue_info",
     *     title="비자발급정보",
     *     @OA\Property(
     *          property="application_no",
     *          type="string",
     *          description="사증 번호, 발급 상태인 경우 필수"
     *     ),
     *     @OA\Property(
     *          property="application_type",
     *          type="integer",
     *          enum={10,20},
     *          description="사정 종류(10:단수, 20:복수), 발급 상태인 경우 필수"
     *     ),
     *     @OA\Property(
     *          property="stay_status",
     *          type="string",
     *          description="체류 자격, 발급 상태인 경우 필수"
     *     ),
     *     @OA\Property(
     *          property="stay_period",
     *          type="integer",
     *          description="체류 기간, 발급 상태인 경우 필수"
     *     ),
     *     @OA\Property(
     *          property="issue_date",
     *          type="string",
     *          format="date",
     *          description="발금일자, 발급 상태인 경우 필수"
     *     ),
     *     @OA\Property(
     *          property="issue_institution",
     *          type="string",
     *          description="발급 기관, 발급 상태인 경우 필수"
     *     ),
     *     @OA\Property(
     *          property="validity_period",
     *          type="string",
     *          format="date",
     *          description="유효기간(만료일), 발급 상태인 경우 필수"
     *     )
     * )
     * @OA\Schema (
     *     schema="vista_status",
     *     title="비자발급 상태",
     *     @OA\Property (
     *          property="status",
     *          ref="#/components/schemas/VisaApplicationStatus"
     *     ),
     *     required={"status"}
     * )
     */
    public function rules(): array {
        $status = $this->enum('status', VisaApplicationStatus::class);
        return [
            'status' => ['required', new Enum(VisaApplicationStatus::class)],
            'application_no' => [
                $status == VisaApplicationStatus::STATUS_ISSUE_COMPLETE ?
                    'required' : 'nullable', 'string'
            ],
            'application_type' => [
                $status == VisaApplicationStatus::STATUS_ISSUE_COMPLETE ?
                    'required' : 'nullable', 'integer', 'in:10,20'
            ],
            'stay_status' => [
                $status == VisaApplicationStatus::STATUS_ISSUE_COMPLETE ?
                    'required' : 'nullable', 'string'
            ],
            'stay_period' => [
                $status == VisaApplicationStatus::STATUS_ISSUE_COMPLETE ?
                    'required' : 'nullable', 'integer'
            ],
            'issue_date' => [
                $status == VisaApplicationStatus::STATUS_ISSUE_COMPLETE ?
                    'required' : 'nullable', 'date', 'date_format:Y-m-d'
            ],
            'issue_institution' => [
                $status == VisaApplicationStatus::STATUS_ISSUE_COMPLETE ?
                    'required' : 'nullable', 'string'
            ],
            'validity_period' => [
                $status == VisaApplicationStatus::STATUS_ISSUE_COMPLETE ?
                    'required' : 'nullable', 'date', 'date_format:Y-m-d'
            ]
        ];
    }
}
