<?php

namespace App\Models;

use App\Lib\CryptDataB64 as CryptData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class VisaAssistant extends Model
{
    use HasFactory;

    protected $fillable = [
        'visa_application_id', 'user_id', 'consulting_user_id', 'assistant_name', 'assistant_birthday',
        'assistant_telephone', 'assistant_relationship'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 비자발급시 사용된 서류작성 도움정보를 리턴한다.
     * @param VisaApplication $visa
     * @return VisaAssistant|null
     */
    public static function findByVisa(VisaApplication $visa) : ?VisaAssistant {
        return static::where('visa_application_id', $visa->id)->get()->first();
    }

    /**
     * 비자발급 서류작성 도움 정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="visa_assistant",
     *     title="서류작성 도우미 정보",
     *     @OA\Property (
     *          property="consulting_user",
     *          type="object",
     *          description="컨설턴트 회원 계정 일련번호",
     *          ref="#/components/schemas/simple_user_info"
     *     ),
     *     @OA\Property (
     *          property="assistant_name",
     *          type="string",
     *          description="도우미 이름",
     *     ),
     *     @OA\Property (
     *          property="assistant_birthday",
     *          type="string",
     *          format="date",
     *          description="도우미 생년월일",
     *     ),
     *     @OA\Property (
     *          property="assistant_telephone",
     *          type="string",
     *          description="도우미 전화번호 (암호화됨)",
     *     ),
     *     @OA\Property (
     *          property="assistant_relationship",
     *          type="string",
     *          description="본인과의 관계",
     *     )
     * )
     */
    public function toInfoArray() : array {
        return [
            'consulting_user' => User::findMe($this->consulting_user_id)?->toSimpleArray(),
            'assistant_name' => $this->assistant_name,
            'assistant_birthday' => $this->assistant_birthday,
            'assistant_telephone' => $this->assistant_telephone ? CryptData::encrypt($this->assistant_telephone) : null,
            'assistant_relationship' => $this->assistant_relationship
        ];
    }
}
