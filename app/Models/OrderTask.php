<?php

namespace App\Models;

use App\Traits\Common\FindMe;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class OrderTask extends Model {
    use HasFactory, FindMe;

    protected $fillable = [
        'order_user_id', 'target_manager_user_id', 'target_user_id', 'task_type', 'model', 'model_id', 'model_data',
        'title', 'body', 'status'
    ];

    /**
     * 요청된 멉무정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="ordered_task",
     *     title="요청된 업무정보",
     *     @OA\Property (property="id", type="integer", description="일련번호"),
     *     @OA\Property (property="order_user", ref="#/components/schemas/simple_user_info"),
     *     @OA\Property (property="manager", ref="#/components/schemas/simple_user_info"),
     *     @OA\Property (property="task_type", type="integer", description="요청 업무 유형"),
     *     @OA\Property (property="model", type="string", description="업무 요청 대상 데이터 모델"),
     *     @OA\Property (property="data", type="object", description="모델 데이터"),
     *     @OA\Property (property="title", type="string", description="업무 요청 제목"),
     *     @OA\Property (property="body", type="string", description="업무 요청 내용"),
     *     @OA\Property (property="status", type="string", description="업무 처리 상태"),
     *     @OA\Property (property="created_at", type="string", format="date", description="생성일시"),
     *     @OA\Property (property="updated_at", type="string", format="date", description="변경일시")
     * )
     */
    public function toInfoArray() : array {
        $ret = [];
        foreach($this->getOriginal() as $key => $value) {
            if($key == 'order_user_id') {
                $order = User::findMe('order_user_id');
                $ret['order'] = $order?->toSimpleArray();
            }
            elseif($key == 'target_manager_user_id') {
                $manager = User::findMe('target_manager_user_id');
                $ret['manager'] = $manager?->toSimpleArray();
            }
            elseif($key == 'target_user_id') {
                $target = User::findMe('target_user_id');
                $ret['target'] = $target?->toSimpleArray();
            }
            elseif($key == 'model_data') $ret['data'] = $value ? json_decode($value) : null;
            elseif($key == 'created_at' || $key == 'updated_at') $ret[$key] = $value->format('Y-m-d H:i:s');
            else $ret[$key] = $value;
        }
        return $ret;
    }
}
