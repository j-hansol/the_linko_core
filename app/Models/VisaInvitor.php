<?php

namespace App\Models;

use App\Lib\CryptDataB64 as CryptData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class VisaInvitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'visa_application_id', 'user_id', 'invitor', 'invitor_relationship', 'invitor_birthday',
        'invitor_registration_no', 'invitor_address', 'invitor_telephone', 'invitor_cell_phone'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * find 대용, 리턴 자료형 명시를 위해 사용
     * @param VisaApplication $visa
     * @return VisaInvitor|null
     */
    public static function findByVisa(VisaApplication $visa) : ?VisaInvitor {
        return static::where('visa_application_id', $visa->id)->get()->first();
    }

    /**
     * 초청자 정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="visa_invitor",
     *     title="초청인 정보",
     *     @OA\Property (
     *          property="invitor",
     *          type="string",
     *          description="초청자/기관 이름",
     *     ),
     *     @OA\Property (
     *          property="invitor_relationship",
     *          type="string",
     *          description="본인과의 관계",
     *     ),
     *     @OA\Property (
     *          property="invitor_birthday",
     *          type="string",
     *          format="date",
     *          description="생년월일",
     *     ),
     *     @OA\Property (
     *          property="invitor_registration_no",
     *          type="string",
     *          description="사업자등록 번호",
     *     ),
     *     @OA\Property (
     *          property="invitor_address",
     *          type="string",
     *          description="주소 (암호화됨)",
     *     ),
     *     @OA\Property (
     *          property="invitor_telephone",
     *          type="string",
     *          description="체류 국내 전화번호 (암호화됨)",
     *     ),
     *     @OA\Property (
     *          property="invitor_cell_phone",
     *          type="string",
     *          description="휴대전화 번호 (암호화됨)",
     *     )
     * )
     */
    public function toInfoArray() : array {
        return [
            'invitor' => $this->invitor,
            'invitor_relationship' => $this->invitor_relationship,
            'invitor_birthday' => $this->invitor_birthday,
            'invitor_registration_no' => $this->invitor_registration_no,
            'invitor_address' => $this->invitor_address ? CryptData::encrypt($this->invitor_address) : null,
            'invitor_telephone' => $this->invitor_telephone ? CryptData::encrypt($this->invitor_telephone) : null,
            'invitor_cell_phone' => $this->invitor_cell_phone ? CryptData::encrypt($this->invitor_cell_phone) : null
        ];
    }
}
