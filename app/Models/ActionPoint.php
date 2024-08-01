<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class ActionPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_user_id', 'type', 'name', 'address', 'longitude', 'latitude', 'radius'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="action_point",
     *     title="활동지점 정보",
     *     @OA\Property (property="id", type="integer", description="일련번호"),
     *     @OA\Property (property="company", ref="#/components/schemas/simple_user_info", description="기업요약정보"),
     *     @OA\Property (property="type", type="integer", description="활동지점 유형"),
     *     @OA\Property (property="name", type="string", description="활동지점 이름"),
     *     @OA\Property (property="address", type="string", description="주소"),
     *     @OA\Property (property="longitude", type="number", format="double", description="경도"),
     *     @OA\Property (property="latitude", type="number", format="double", description="위도"),
     *     @OA\Property (property="radius", type="number", format="double", description="활동반경"),
     * )
     */
    public function toInfoArray() : array {
        return [
            'id' => $this->id,
            'company' => User::findMe($this->company_user_id),
            'type' => $this->type,
            'name' => $this->name,
            'address' => $this->address,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'radius' => $this->radius
        ];
    }
}
