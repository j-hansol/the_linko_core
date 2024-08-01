<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class ConsultingMessage extends Model {
    use HasFactory;

    protected $fillable = ['visa_application_id', 'user_id', 'title', 'message'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * find 대용, 리턴 타입 설정 문제로 사용
     * @param $id
     * @return User|null
     */
    public static function findMe(?int $id = null) : ?User {
        if(!$id) return null;
        return static::find($id);
    }

    /**
     * 비자발급 정보에 첨부할 목적으로 배열을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="visa_consulting_message",
     *     title="비자발급 컨설팅 메시지",
     *     @OA\Property (
     *          property="id",
     *          type="integer",
     *          description="일련번호"
     *     ),
     *     @OA\Property (property="author", ref="#/components/schemas/simple_user_info"),
     *     @OA\Property (
     *          property="title",
     *          type="string",
     *          description="제목"
     *     ),
     *     @OA\Property (
     *          property="message",
     *          type="string",
     *          description="메시지"
     *     ),
     *     @OA\Property (
     *          property="created_at",
     *          type="string",
     *          format="date-time",
     *          description="메시지"
     *     ),
     *     @OA\Property (
     *          property="updated_at",
     *          type="string",
     *          format="date-time",
     *          description="메시지"
     *     ),
     * )
     */
    public function toInfoArray() : array {
        $ret = $this->toArray();
        unset($ret['id']); unset($ret['user_id']); unset($ret['visa_application_id']);
        return ['author' => User::findMe($this->user_id)->toSimpleArray()] + $ret;
    }

    /**
     * 지정 비자관련 메시지 목록을 배열로 리턴한다.
     * @param VisaApplication $visa
     * @return array|null
     * @OA\Schema(
     *     schema="visa_consulting_message_list",
     *     title="컨설팅 메시지 목록",
     *     @OA\Property(
     *          property="items",
     *          type="array",
     *          description="컨설팅 메시지 목록",
     *          @OA\Items(
     *              type="object",
     *              allOf={@OA\Schema(ref="#/components/schemas/visa_consulting_message")}
     *          )
     *     )
     * )
     */
    public static function listByVisa(VisaApplication $visa) : ?array {
        $messages = static::where('visa_application_id', $visa->id)
            ->orderBy('id', 'desc')
            ->get();

        if($messages->isNotEmpty()) {
            $ret = [];
            foreach($messages as $message) $ret[] = $message->toInfoArray();
            return $ret;
        }
        else return null;
    }
}
