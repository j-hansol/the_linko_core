<?php

namespace App\Models;

use App\Http\JsonResponses\Common\Data;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class VisaDocumentType extends Model {
    use HasFactory;

    protected $fillable = [
        'name', 'en_name', 'description', 'en_description', 'required', 'weight',' active'
    ];

    public $timestamps = false;

    /**
     * find 대용, 리턴 타입 설정 문제로 사용
     * @param int|null $id
     * @return VisaDocumentType|null
     */
    public static function findMe(?int $id = null) : ?VisaDocumentType {
        if(!$id) return null;
        else return static::find($id);
    }

    /**
     * 현재 문서 유형 정보로 응답한다.
     * @return JsonResponse
     * @OA\Schema(
     *     schema="visa_document_type",
     *     title="비자발급시 필요한 문서 유형",
     *     @OA\Property(
     *          property="id",
     *          type="integer",
     *          description="일련번호"
     *     ),
     *     @OA\Property(
     *          property="name",
     *          type="string",
     *          description="문서유형 이름"
     *     ),
     *     @OA\Property(
     *          property="en_name",
     *          type="string",
     *          description="문서유형 이름(영문)"
     *     ),
     *     @OA\Property(
     *          property="description",
     *          type="string",
     *          description="유형 설명"
     *     ),
     *     @OA\Property(
     *          property="en_description",
     *          type="string",
     *          description="유형 설명(영문)"
     *     ),
     *     @OA\Property(
     *          property="active",
     *          type="integer",
     *          description="사용여부"
     *     ),
     *     @OA\Property(
     *         property="required",
     *         type="integer",
     *         description="필수 업로드 파일 여부 (1: 필수, 0:선택)"
     *    ),
     *    @OA\Property(
     *        property="weight",
     *        type="integer",
     *        description="정렬을 위한 가중치 (높은 값을 가진 자료가 앞에 출력됨)"
     *   )
     * )
     */
    public function response() : JsonResponse {
        return new Data($this->toArray());
    }

    /**
     * 간단한 문서 정보를 배열로 리턴헌다.
     * @return array
     * @OA\Schema(
     *     schema="simple_visa_document_type",
     *     title="비자발급시 필요한 문서 유형 (간단한 정보)",
     *     @OA\Property(
     *          property="id",
     *          type="integer",
     *          description="일련번호"
     *     ),
     *     @OA\Property(
     *          property="name",
     *          type="string",
     *          description="문서유형 이름"
     *     ),
     *     @OA\Property(
     *          property="en_name",
     *          type="string",
     *          description="문서유형 이름(영문)"
     *     )
     *  )
     * /
     */
    public function toSimpleArray() : array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'en_name' => $this->en_name
        ];
    }
}
