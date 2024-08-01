<?php

namespace App\Models;

use App\Lib\EvaluationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class EvalItem extends Model {
    use HasFactory;

    private ?array $parsed_answers = [];
    private ?int $parsed_answer_count = 0;
    private bool $is_parsed = false;

    protected $fillable = [
        'eval_info_id', 'type', 'question', 'answers'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 답변 셋트를 파싱한다.
     * @return void
     */
    public function parseAnswer() : void {
        if($this->is_parsed) return;

        if($this->type >= EvaluationType::WORD->value) {
            $this->parsed_answers = [];
            $this->parsed_answer_count = 0;
        }
        else {
            if($this->answers) {
                $items = explode(';', $this->answers);
                foreach($items as $item) {
                    $t = explode(':', $item);
                    if(count($t) == 2) {
                        $this->parsed_answers[] = [
                            'label' => trim($t[0]),
                            'value' => trim($t[1])
                        ];
                    }
                }
                $this->parsed_answer_count = count($this->parsed_answers);
                $this->is_parsed = true;
            }
            else {
                $this->parsed_answers = [];
                $this->parsed_answer_count = 0;
                $this->is_parsed = true;
            }
        }
    }

    /**
     * 파싱된 답변셋트 목록을 리턴한다.
     * @return array
     */
    public function getAnswers() : array {
        return $this->parsed_answers ?? [];
    }

    /**
     * 답변 셋트의 개수를 리턴한다.
     * @return int
     */
    public function getAnswerCount() : int {
        return $this->parsed_answer_count ?? 0;
    }

    /**
     * 설문 항목 정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="eval_item",
     *     description="설문항목",
     *     @OA\Property(property="id",type="integer",description="일련번호"),
     *     @OA\Property(property="type",type="integer",description="설문 유형", ref="#/components/schemas/EvaluationType"),
     *     @OA\Property(property="question",type="string",description="질문"),
     *     @OA\Property(property="answers",type="string",description="선택 가능 답안"),
     * )
     */
    public function toArrayForEvalInfo() : array {
        $t = $this->toArray();
        unset($t['eval_info_id']);
        unset($t['created_at']);
        unset($t['updated_at']);
        return $t;
    }

    /**
     * 선택 가능한 값 목록을 리턴한다.
     * @return array
     */
    public function getSelectAbleValues() : array {
        if(!$this->is_parsed) $this->parseAnswer();;
        $t = [];
        foreach($this->parsed_answers as $answer) if(isset($answer['value'])) $t[] = $answer['value'];
        return $t;
    }
}
