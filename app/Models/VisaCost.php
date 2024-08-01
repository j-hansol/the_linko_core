<?php

namespace App\Models;

use App\Lib\CryptDataB64 as CryptData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class VisaCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'visa_application_id', 'user_id', 'travel_costs', 'payer_name', 'payer_relationship', 'support_type',
        'payer_contact'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 비자발급시 사용한 비용정보를 리턴한다.
     * @param VisaApplication $visa
     * @return VisaCost|null
     */
    public static function findByVisa(VisaApplication $visa) : ?VisaCost {
        return static::where('visa_application_id', $visa->id)->get()->first();
    }

    /**
     * 비자발급시 사용한 비용정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="visa_cost",
     *     title="경비지출 정보",
     *     @OA\Property (
     *          property="travel_costs",
     *          type="number",
     *          format="double",
     *          description="예상 경비(달러기준)",
     *     ),
     *     @OA\Property (
     *          property="payer_name",
     *          type="string",
     *          description="경비 지원자 이름",
     *     ),
     *     @OA\Property (
     *          property="payer_relationship",
     *          type="string",
     *          description="본인과의 관계",
     *     ),
     *     @OA\Property (
     *          property="support_type",
     *          type="string",
     *          description="지원 유형",
     *     ),
     *     @OA\Property (
     *          property="payer_contact",
     *          type="string",
     *          description="경비 지원자 연락처 (암호화됨)",
     *     )
     * )
     */
    public function toInfoArray() : array {
        return [
            'travel_costs' => $this->travel_costs,
            'payer_name' => $this->payer_name,
            'payer_relationship' => $this->payer_relationship,
            'support_type' => $this->support_type,
            'payer_contact' => $this->payer_contact ? CryptData::encrypt($this->payer_contact) : null
        ];
    }
}
