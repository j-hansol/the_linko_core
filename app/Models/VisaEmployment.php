<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class VisaEmployment extends Model
{
    use HasFactory;

    protected $fillable = [
        'visa_application_id', 'user_id', 'job', 'other_detail', 'org_name', 'position_course', 'org_address', 'org_telephone'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 비자발급에 사용된 직업정보를 리턴한다.
     * @param VisaEducation $visa
     * @return VisaEmployment|null
     */
    public static function findByVisa(VisaApplication $visa) : ?VisaEmployment {
        return static::where('visa_application_id', $visa->id)->get()->first();
    }

    /**
     * 직업정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="visa_employment",
     *     title="직업정보",
     *     @OA\Property (
     *          property="job",
     *          type="integer",
     *          description="직업구분",
     *     ),
     *     @OA\Property (
     *          property="other_detail",
     *          type="string",
     *          description="기타 상세 내용",
     *     ),
     *     @OA\Property (
     *          property="org_name",
     *          type="string",
     *          description="직장명/학교명",
     *     ),
     *     @OA\Property (
     *          property="position_course",
     *          type="string",
     *          description="직급/직위/과정",
     *     ),
     *     @OA\Property (
     *          property="org_address",
     *          type="string",
     *          description="직작/학교 주소",
     *     ),
     *     @OA\Property (
     *          property="org_telephone",
     *          type="string",
     *          description="직장/학교 전화번호",
     *     )
     * )
     */
    public function toInfoArray() : array {
        return [
            'job' => $this->job,
            'org_name' => $this->org_name,
            'position_course' => $this->position_course,
            'org_address' => $this->org_address,
            'org_telephone' => $this->org_telephone
        ];
    }
}
