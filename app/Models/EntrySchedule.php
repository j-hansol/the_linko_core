<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class EntrySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id', 'entry_date', 'entry_limit', 'target_datetime', 'target_place'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 입국일정 정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="entry_info_data",
     *     title="입국일정 정보 데이터",
     *     @OA\Property (
     *          property="contract_id",
     *          type="integer",
     *          description="계약정보 일련번호"
     *     ),
     *     @OA\Property (
     *          property="entry_date",
     *          type="string",
     *          format="date",
     *          description="입국일자"
     *     ),
     *     @OA\Property (
     *          property="entry_limit",
     *          type="integer",
     *          description="입국 정원"
     *     ),
     *     @OA\Property (
     *          property="target_datetime",
     *          type="string",
     *          format="date-time",
     *          description="집결지 도착 일시"
     *     ),
     *     @OA\Property (
     *          property="target_place",
     *          type="string",
     *          description="집결지"
     *     )
     * )
     * @OA\Schema (
     *     schema="entry_info",
     *     title="입국일정 정보",
     *     allOf={
     *          @OA\Schema (ref="#/components/schemas/entry_info_data"),
     *          @OA\Schema (ref="#/components/schemas/model_timestamps")
     *     }
     * )
     */
    public function toInfoArray() : array {
        return $this->toArray();
    }

    /**
     * 지정 일련번호의 데이터를 리턴한다.
     * @param int|null $id
     * @return EntrySchedule|null
     */
    public static function findMe(?int $id) : ?EntrySchedule {
        if(!$id) return null;
        return static::find($id);
    }
}
