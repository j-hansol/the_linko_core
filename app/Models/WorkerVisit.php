<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class WorkerVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'country_id', 'visit_purpose', 'entry_date', 'departure_date', 'period_of_stay', 'reference_count'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * find 대용, 리턴 자료형 명시적 사용
     * @param int|null $id
     * @return WorkerVisit|null
     */
    public static function findMe(?int $id = null) : ?WorkerVisit {
        if(!$id) return null;
        return static::find($id);
    }

    /**
     * 방문정보를 배열로 전달한다. 기본적으로 $include_country 값을 참으로지정하면 국가정보를 포함하여 리턴한다.
     * @param bool $include_country
     * @return array
     * @OA\Schema(
     *     schema="worker_visited_country",
     *     title="근로자 방문 국가정보",
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
     *          property="visit_purpose",
     *          type="string",
     *          description="방문목적"
     *     ),
     *     @OA\Property(
     *          property="entry_date",
     *          type="string",
     *          format="date",
     *          description="입국일자"
     *     ),
     *     @OA\Property(
     *          property="departure_date",
     *          type="string",
     *          format="date",
     *          description="출국일자"
     *     ),
     *     @OA\Property(
     *          property="reference_count",
     *          type="integer",
     *          description="쵬조 수"
     *     )
     * )
    @OA\Schema(
     *     schema="worker_visited_korea",
     *     title="근로자 한국 방문정보",
     *     @OA\Property(
     *          property="id",
     *          type="integer",
     *          description="일련번호"
     *     ),
     *     @OA\Property(
     *          property="visit_purpose",
     *          type="string",
     *          description="방문목적"
     *     ),
     *     @OA\Property(
     *          property="entry_date",
     *          type="string",
     *          format="date",
     *          description="입국일자"
     *     ),
     *     @OA\Property(
     *          property="departure_date",
     *          type="string",
     *          format="date",
     *          description="출국일자"
     *     ),
     *     @OA\Property(
     *          property="reference_count",
     *          type="integer",
     *          description="쵬조 수"
     *     )
     * )
     */
    public function toInfoArray(bool $include_country = true) : array {
        $def = [];
        if($include_country) $def['country'] = Country::findMe($this->country_id)->toArray();
        return $def + [
            'id' => $this->id,
            'visit_purpose' => $this->visit_purpose,
            'entry_date' => $this->entry_date,
            'departure_date' => $this->departure_date,
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
