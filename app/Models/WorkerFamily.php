<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class WorkerFamily extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'country_id', 'name', 'birthday', 'text_birthday', 'relationship', 'reference_count'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * find 대용, 리턴타입 명시를 위해 사용
     * @param int|null $id
     * @return WorkerFamily|null
     */
    public static function findMe(?int $id = null) : ?WorkerFamily {
        if(!$id) return null;
        return static::find($id);
    }

    /**
     * 가족정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="worker_family",
     *     title="근로자 가족정보",
     *     @OA\Property(
     *          property="id",
     *          type="integer",
     *          description="일련번호"
     *     ),
     *     @OA\Property(
     *          property="country",
     *          type="object",
     *          ref="#/components/schemas/country",
     *          description="방문 국가정보 일련번호"
     *     ),
     *     @OA\Property(
     *          property="name",
     *          type="string",
     *          description="이름"
     *     ),
     *     @OA\Property(
     *          property="birthday",
     *          type="string",
     *          format="date",
     *          description="생년월일"
     *     ),
     *     @OA\Property(
     *          property="relationship",
     *          type="string",
     *          description="본인과의 관계"
     *     ),
     *     @OA\Property(
     *          property="reference_count",
     *          type="integer",
     *          description="쵬조 수"
     *     )
     * )
     */
    public function toInfoArray() : array {
        return [
            'id' => $this->id,
            'country' => Country::findMe($this->country_id)->toArray(),
            'name' => $this->name,
            'birthday' => $this->birthday,
            'relationship' => $this->relationship
        ];
    }

    /**
     * 참조 수를 증가시킨다.
     * @return int
     */
    public function reference() : int {
        return $this->increment('reference_count');
    }

    /**
     * 참조 수를 감소시킨다.
     * @return int
     */
    public function unReference() : int {
        if($this->reference_count > 0) return $this->decrement('reference_count');
        else return 0;
    }
}
