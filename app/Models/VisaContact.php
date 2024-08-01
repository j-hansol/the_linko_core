<?php

namespace App\Models;

use App\Lib\CryptDataB64 as CryptData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class VisaContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'visa_application_id',  'user_id',  'home_address',  'current_address',  'cell_phone',  'email', 'emergency_full_name',
        'emergency_country_id', 'emergency_telephone', 'emergency_relationship'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 비자 발급 시 사용된 연락처 정보를 리턴한다.
     * @param VisaApplication $visa
     * @return VisaContact|null
     */
    public static function findByVisa(VisaApplication $visa) : ?VisaContact {
        return static::where('visa_application_id', $visa->id)->get()->first();
    }

    /**
     * 비자발급 시 사용한 연락처 정보를 배열로 리턴한다. 연락처 정보는 암호화한다.
     * @return array
     * @OA\Schema (
     *     schema="visa_contact",
     *     title="연락처정보",
     *     @OA\Property (
     *          property="home_address",
     *          type="string",
     *          description="공식 등록된 거주지 주소 (암호화됨)",
     *     ),
     *     @OA\Property (
     *          property="current_address",
     *          type="string",
     *          description="현 거주지 주소 (암호화됨)",
     *     ),
     *     @OA\Property (
     *          property="cell_phone",
     *          type="string",
     *          description="류대전화 번호 (암호화됨)",
     *     ),
     *     @OA\Property (
     *          property="email",
     *          type="string",
     *          description="전자우편 주소 (암호화됨)",
     *      ),
     *     @OA\Property (
     *          property="emergency_full_name",
     *          type="string",
     *          description="비상연락처 이름",
     *     ),
     *     @OA\Property (
     *          property="emergency_country",
     *          type="object",
     *          description="비상연락처 국가",
     *          ref="#/components/schemas/country"
     *     ),
     *     @OA\Property (
     *          property="emergency_telephone",
     *          type="string",
     *          description="비상연락처 전화번호 (암호화됨)",
     *     ),
     *     @OA\Property (
     *          property="emergency_relationship",
     *          type="string",
     *          description="비상연락처 관계",
     *     )
     * )
     */
    public function toInfoArray() : array {
        return [
            'home_address' => $this->home_address ? CryptData::encrypt($this->home_address) : null,
            'current_address' => $this->current_address ? CryptData::encrypt($this->current_address) : null,
            'cell_phone' => $this->cell_phone ? CryptData::encrypt($this->cell_phone) : null,
            'email' => $this->email ? CryptData::encrypt($this->email) : null,
            'emergency_full_name' => $this->emergency_full_name,
            'emergency_country' => Country::findMe($this->emergency_country_id)->toArray(),
            'emergency_telephone' => $this->emergency_telephone ? CryptData::encrypt($this->emergency_telephone) : null,
            'emergency_relationship' => $this->emergency_relationship,
        ];
    }
}
