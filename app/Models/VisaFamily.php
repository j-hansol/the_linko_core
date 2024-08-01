<?php

namespace App\Models;

use App\Lib\CryptDataB64 as CryptData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class VisaFamily extends Model
{
    use HasFactory;

    protected $fillable = [
        'visa_application_id', 'user_id', 'marital_status', 'spouse_family_name', 'spouse_given_name',
        'spouse_birthday', 'text_spouse_birthday', 'spouse_nationality_id', 'spouse_nationality',
        'spouse_residential_address', 'spouse_contact_no', 'number_of_children'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 비자발급시 사용된 가족사항 정보를 리턴한다.
     * @param VisaApplication $visa
     * @return VisaFamily|null
     */
    public static function findByVisa(VisaApplication $visa) : ?VisaFamily {
        return static::where('visa_application_id', $visa->id)->get()->first();
    }

    /**
     * 비자발급시 사용된 가족사항 정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="visa_family",
     *     title="혼인관계 및 가족정보",
     *     @OA\Property (
     *          property="marital_status",
     *          type="integer",
     *          description="결혼 여부 또는 상태",
     *     ),
     *     @OA\Property (
     *          property="spouse_family_name",
     *          type="string",
     *          description="배우자 성",
     *     ),
     *     @OA\Property (
     *          property="spouse_given_name",
     *          type="string",
     *          description="배우자 이름",
     *     ),
     *     @OA\Property (
     *          property="spouse_birthday",
     *          type="string",
     *          format="date",
     *          description="배우자 생년월일",
     *     ),
     *     @OA\Property (
     *          property="spouse_nationality",
     *          type="object",
     *          description="배우자 국적",
     *          ref="#/components/schemas/country"
     *     ),
     *     @OA\Property (
     *          property="spouse_residential_address",
     *          type="string",
     *          description="배우자 거주지 주소 (암호화됨)",
     *     ),
     *     @OA\Property (
     *          property="spouse_contact_no",
     *          type="string",
     *          description="배우자 연락처 (암호화됨)",
     *     ),
     *     @OA\Property (
     *          property="number_of_children",
     *          type="integer",
     *          description="자녀 수",
     *     )
     * )
     */
    public function toInfoArray() : array {
        return [
            'marital_status' => $this->marital_status,
            'spouse_family_name' => $this->spouse_family_name,
            'spouse_given_name' => $this->spouse_given_name,
            'spouse_birthday' => $this->spouse_birthday,
            'spouse_nationality' => $this->spouse_nationality_id ? Country::findMe($this->spouse_nationality_id)->toArray() : null,
            'spouse_residential_address' => $this->spouse_residential_address ? CryptData::encrypt($this->spouse_residential_address) : null,
            'spouse_contact_no' => $this->spouse_contact_no ? CryptData::encrypt($this->spouse_contact_no) : null,
            'number_of_children' => $this->number_of_children
        ];
    }
}
