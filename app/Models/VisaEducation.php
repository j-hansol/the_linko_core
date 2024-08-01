<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class VisaEducation extends Model
{
    use HasFactory;

    protected $fillable = [
        'visa_application_id', 'user_id', 'highest_degree', 'other_detail', 'school_name', 'school_location'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 비자블급 시 사용한 학력정보를 리턴한다.
     * @param VisaApplication $visa
     * @return VisaEducation|null
     */
    public static function findByVisa(VisaApplication $visa) : ?VisaEducation {
        return static::where('visa_application_id', $visa->id)->get()->first();
    }

    /**
     * 비자블급 시 사용한 학력정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="visa_education",
     *     title="학력정보",
     *     @OA\Property (
     *          property="highest_degree",
     *          type="integer",
     *          description="최종학력 구분",
     *     ),
     *     @OA\Property (
     *          property="other_detail",
     *          type="string",
     *          description="기타의 경우 설명",
     *     ),
     *     @OA\Property (
     *          property="school_name",
     *          type="string",
     *          description="학교명",
     *     ),
     *     @OA\Property (
     *          property="school_location",
     *          type="string",
     *          description="학교 소재지",
     *     )
     * )
     */
    public function toInfoArray() : array {
        return [
            'highest_degree' => $this->highest_degree,
            'other_detail' => $this->other_detail,
            'school_name' => $this->school_name,
            'school_location' => $this->school_location
        ];
    }
}
