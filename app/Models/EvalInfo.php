<?php

namespace App\Models;

use App\Http\JsonResponses\Common\Data;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class EvalInfo extends Model {
    use HasFactory;

    private ?Collection $eval_items = null;
    private ?int $eval_count = null;

    protected $fillable = [
        'title', 'target', 'description', 'items', 'active'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 평가 항목을 리턴한다.
     * @return Collection|null
     */
    public function getEvalItems() : ?Collection {
        if($this->eval_items) return $this->eval_items;
        return $this->eval_items = $this->hasMany(EvalItem::class, 'eval_info_id')->get();
    }

    /**
     * 평가 아이템 개수를 리턴한다.
     * @return int
     */
    public function getEvalItemCount() : int {
        if($this->eval_count != null) return $this->eval_count;
        else {
            $this->eval_items = $this->getEvalItems();
            return $this->eval_count = $this->eval_items->count();
        }
    }

    /**
     * 평가 참여자 수를 리턴한다.
     * @return int
     */
    public function getEvaluationCount() : int {
        return $this->hasMany(Evaluation::class, 'eval_info_id')->count();
    }

    /**
     * 수정 또는 삭제 가능 여부를 판단한다.
     * @return bool
     */
    public function isEditable() : bool {
        return $this->getEvaluationCount() == 0;
    }

    /**
     * 현재 사용중인 평가 마스터 정보를 리턴한다.
     * @return EvalInfo|null
     */
    public static function getActiveEvalInfo() : ?EvalInfo {
        return static::query()
            ->where('active', 1)
            ->get()->first();
    }

    /**
     * 설문 항목을 포함하여 배열로 리턴한다.
     * @return array
     */
    public function toArrayIncludeItems() : array {
        $eval_items = $this->getEvalItems();
        $items = [];
        if($eval_items->isNotEmpty()) foreach($eval_items as $item) $items[] = $item->toArray();
        $tr = $this->toArray();
        $tr['items'] = $items;
        return $tr;
    }

    /**
     * 설문 항목을 포함한 설문정보로 응답한다.
     * @return JsonResponse
     * @OA\Schema(
     *     schema="eval_info_include_item",
     *     title="평가 항목을 포함한 평가 설문 정보",
     *     @OA\Property(property="id",type="integer",description="일련번호"),
     *     @OA\Property(property="title",type="string",description="제목"),
     *     @OA\Property(property="rarget",type="integer",description="평가대상", ref="#/components/schemas/EvalTarget"),
     *     @OA\Property(property="description",type="string",description="설명"),
     *     @OA\Property(property="items",type="array",@OA\Items(ref="#/components/schemas/eval_item")),
     *     @OA\Property(property="active",type="integer",description="사용 가능 여부"),
     *     @OA\Property (property="created_at", type="string",format="date-time",description="생성일시"),
     *     @OA\Property (property="updated_at",type="string",format="date-time",description="수정일시")
     * )
     */
    public function responseWithItems() : JsonResponse {
        return new Data($this->toArrayIncludeItems());
    }

    /**
     * 지정 ID의 정보를 리턴한다.
     * @param int|null $id
     * @return EvalInfo|null
     */
    public static function findMe(?int $id) : ?EvalInfo {
        if(!$id) return null;
        return static::find($id);
    }
}
