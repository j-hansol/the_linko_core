<?php

namespace App\Models;

use App\Lib\CryptDataB64 as CryptData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class VisaProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'visa_application_id', 'user_id', 'family_name', 'given_names', 'hanja_name', 'identity_no', 'sex', 'birthday',
        'text_birthday', 'nationality_id', 'nationality', 'birth_country_id', 'another_nationality_ids',
        'other_nationality', 'old_family_name', 'old_given_names',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 해당 비자 발급 시점의 프로필 정보를 리턴한다.
     * @param VisaApplication $visa
     * @return VisaProfile|null
     */
    public static function findByVisa(VisaApplication $visa) : ?VisaProfile {
        return static::where('visa_application_id', $visa->id)->get()->first();
    }

    /**
     * 프로필 정보를 배열로 리턴한다. 단 신분증 번호는 암호화하여 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="visa_profile",
     *     title="비자 프로필",
     *     @OA\Property (
     *          property="family_name",
     *          type="string",
     *          description="성",
     *     ),
     *     @OA\Property (
     *          property="given_names",
     *          type="string",
     *          description="이름",
     *     ),
     *     @OA\Property (
     *          property="hanja_name",
     *          type="string",
     *          description="한자 이름",
     *     ),
     *     @OA\Property (
     *          property="identity_no",
     *          type="string",
     *          description="신분증 번호 (암호화됨)",
     *     ),
     *     @OA\Property (
     *          property="sex",
     *          type="string",
     *          description="성별",
     *     ),
     *     @OA\Property (
     *          property="birthday",
     *          type="string",
     *          format="date",
     *          description="생년월일",
     *     ),
     *     @OA\Property (
     *          property="nationality",
     *          description="국적",
     *          type="object",
     *          ref="#/components/schemas/country"
     *     ),
     *     @OA\Property (
     *          property="birth_country",
     *          type="object",
     *          description="출생국가",
     *          ref="#/components/schemas/country"
     *     ),
     *     @OA\Property (
     *          property="another_nationality_ids",
     *          type="array",
     *          description="그 외 다른 국적",
     *          @OA\Items (
     *              type="object",
     *              ref="#/components/schemas/country"
     *          )
     *     ),
     *     @OA\Property (
     *          property="old_family_name",
     *          type="string",
     *          description="이전 성"
     *     ),
     *     @OA\Property (
     *          property="old_given_names",
     *          type="string",
     *          description="이전 이름"
     *     ),
     * )
     */
    public function toInfoArray() : array {
        $ret = $this->toArray();
        unset($ret['visa_application_id']);
        unset($ret['user_id']);
        unset($ret['created_at']);
        unset($ret['updated_at']);
        unset($ret['nationality_id']);
        unset($ret['birth_country_id']);
        unset($ret['id']);
        $ret['nationality'] = Country::findMe($this->nationality_id)?->toArray();
        $ret['birth_country'] = Country::findMe($this->birth_country_id)?->toArray();
        $ret['identity_no'] = CryptData::encrypt($this->identity_no);
        $ret['another_nationality_ids'] = $this->another_nationality_ids ? json_decode($this->another_nationality_ids) : [];
        return $ret;
    }

    /**
     * @param VisaApplication $visa
     * @param VisaApplication $visa
     * @param User $user
     * @return void
     */
    public static function createFrom(VisaApplication $visa, User $user) : void {
        static::create(
            ['visa_application_id' => $visa->id, 'user_id' => $user->id, 'nationality_id' => $user->country_id]
            + $user->getOriginal()
        );
    }
}
