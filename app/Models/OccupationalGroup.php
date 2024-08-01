<?php

namespace App\Models;

use App\Http\JsonResponses\Common\Data;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class OccupationalGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        // 직업군 정보
        'group_code', 'name', 'en_name', 'description', 'en_description',

        // 기타정보
        'parent_id', 'leaf_node', 'active', 'is_education_part'
    ];

    public $timestamps = false;

    /**
     * 직업군 정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="occupational_group",
     *     title="직업군정보",
     *     @OA\Property (
     *          property="id",
     *          type="integer",
     *          description="일련번호"
     *     ),
     *     @OA\Property (
     *          property="group_code",
     *          type="string",
     *          description="그룹코드"
     *     ),
     *     @OA\Property (
     *          property="name",
     *          type="string",
     *          description="직업군 이름"
     *     ),
     *     @OA\Property (
     *          property="en_name",
     *          type="string",
     *          description="직업군 이름 (영문)"
     *     ),
     *     @OA\Property (
     *          property="description",
     *          type="string",
     *          description="직업군 설명"
     *     ),
     *     @OA\Property (
     *          property="en_description",
     *          type="string",
     *          description="직업군 설명 (영문)"
     *     ),
     *     @OA\Property (
     *          property="active",
     *          type="boolean",
     *          description="사용 여부"
     *     ),
     *     @OA\Property (
     *          property="is_education_part",
     *          type="boolean",
     *          description="교육분야 사용 여부"
     *     )
     * )
     */
    public function toInfoArray() : array {
        return $this->toArray();
    }

    /**
     * 적업군 데이터를 Json 문자열로 응답한다.
     * @return JsonResponse
     */
    public function response() : JsonResponse {
        return new Data($this->toInfoArray());
    }

    /**
     * 지정 id의 정보를 검색하여 리턴한다.
     * @param int $id
     * @return OccupationalGroups|null
     */
    public static function findMe(int $id) : ?OccupationalGroup {
        return static::find($id);
    }
}
