<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class VisaApplicationIssuedInfo extends Model
{
    use HasFactory;

    const TYPE_SINGLE       = 10;
    const TYPE_MULTIPLE     = 20;

    protected $fillable = [
        'visa_application_id', 'user_id', 'attorney_user_id', 'application_no', 'application_type', 'stay_status',
        'stay_period', 'issue_date', 'issue_Institution', 'validity_period',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 해당 비자정보의 발급정보를 리턴한다.
     * @param VisaApplication $visa
     * @return VisaApplicationIssuedInfo|null
     */
    public static function findByVisa(VisaApplication $visa) : ?VisaApplicationIssuedInfo {
        return static::where('visa_application_id', $visa->id)->get()->first();
    }

    /**
     * 비자발급정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="visa_issued_info",
     *     title="비자발급정보",
     *     @OA\Property (property="attorney_user", ref="#/components/schemas/simple_user_info"),
     *     @OA\Property (
     *          property="application_no",
     *          type="string",
     *          description="비자 번호",
     *     ),
     *     @OA\Property (
     *          property="application_type",
     *          type="string",
     *          description="비자 종류",
     *     ),
     *     @OA\Property (
     *          property="stay_status",
     *          type="string",
     *          description="체류 자격",
     *     ),
     *     @OA\Property (
     *          property="stay_period",
     *          type="string",
     *          description="체류 기간",
     *     ),
     *     @OA\Property (
     *          property="issue_date",
     *          type="string",
     *          description="발급 일자",
     *     ),
     *     @OA\Property (
     *          property="issue_Institution",
     *          type="string",
     *          description="발급 기관",
     *     ),
     *     @OA\Property (
     *          property="validity_period",
     *          type="string",
     *          description="유효 기간",
     *     )
     * )
     */
    public function toInfoArray() : array {
        return [
            'attorney_user' => User::findMe($this->attorney_user_id)->toSimpleArray(),
            'application_no' => $this->application_no,
            'application_type' => $this->application_type,
            'stay_status' => $this->stay_status,
            'stay_period' => $this->stay_period,
            'issue_date' => $this->issue_date,
            'issue_Institution' => $this->issue_Institution,
            'validity_period' => $this->validity_period,
        ];
    }
}
