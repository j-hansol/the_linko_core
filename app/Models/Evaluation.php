<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class Evaluation extends Model {
    use HasFactory;

    private ?array $parsed_answers = [];
    private ?int $parsed_answer_count = 0;

    protected $fillable = [
        'contract_id', 'user_id', 'target_user_id', 'assigned_worker_id', 'eval_info_id', 'answers',
        'eval_result'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 평가정보를 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="evaluation",
     *     title="평가결과정보",
     *     @OA\Property (
     *         property="id",
     *         type="integer",
     *         description="평가결과정보 일련번호"
     *     ),
     *     @OA\Property (
     *         property="contract_id",
     *         type="integer",
     *         description="계약정보 일련번호"
     *     ),
     *     @OA\Property (
     *         property="assigned_worker_id",
     *         type="integer",
     *         description="근로자 배정정보 일련번호"
     *     ),
     *     @OA\Property (
     *         property="eval_info_id",
     *         type="integer",
     *         description="평가설문 마스트 일련번호"
     *     ),
     *     @OA\Property (
     *         property="author",
     *         ref="#/components/schemas/simple_user_info",
     *         description="평가결과 등록자 계정 일련번호"
     *     ),
     *     @OA\Property (
     *         property="target",
     *         ref="#/components/schemas/simple_user_info",
     *         description="평가대상 계정 일련번호"
     *     ),
     *     @OA\Property (
     *         property="answers",
     *         type="array",
     *         @OA\Items (
     *             type="object",
     *             @OA\Property (
     *                 property="item_id",
     *                 type="integer",
     *                 description="설문 항목 일련번호",
     *             ),
     *             @OA\Property (
     *                 property="answer",
     *                 type="string",
     *                 description="응답 내용 (5점형의 경우 숫자 가능)",
     *             )
     *         ),
     *         description="응답 결과"
     *     ),
     *     @OA\Property (
     *         property="created_at",
     *         type="string",
     *         type="date-time",
     *          description="생성일시"
     *     ),
     *     @OA\Property (
     *         property="updated_at",
     *         type="string",
     *         type="date-time",
     *         description="수정일시"
     *     )
     * )
     */
    public function toInfoArray() : array {
        return [
            'id' => $this->id,
            'contract_id' => $this->contract_id,
            'assigned_worker_id' => $this->assigned_worker_id,
            'eval_info_id' => $this->eval_info_id,
            'author' => User::findMe($this->user_id)?->toSimpleArray(),
            'target' => User::findMe($this->target_user_id)?->toSimpleArray(),
            'answers' => $this->answers ? json_decode($this->answers) : null,
            'eval_result' => $this->eval_result,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
