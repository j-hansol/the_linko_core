<?php

namespace App\DTOs\V1;

use App\Lib\EvaluationType;
use App\Models\EvalInfo;
use App\Models\EvalItem;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class EvaluationAnswerDto {
    // 속성
    private array $answers = [];
    private int $eval_info_id = 0;
    private int $eval_result = 0;

    // 생성자
    /**
     * @param string $param
     * @throws HttpErrorsException
     */
    function __construct(string $param) {
        // Json 파싱 및 점검
        $json = json_decode($param);
        if(!$json) throw HttpErrorsException::getInstance([__('errors.json.parse_error')], 400);

        // 구문 점검
        if(!$json['eval_info_id'] || !$json['answers'])
            throw HttpErrorsException::getInstance([__('errors.eval_info.invalid_evaluation_type')], 400);
        if(!is_array($json['answers']))
            throw HttpErrorsException::getInstance([__('errors.eval_info.invalid_answer')], 400);

        // 평가 설문 점검
        $info = EvalInfo::findMe($json['eval_info_id']);
        if(!$info)
            throw HttpErrorsException::getInstance([__('errors.eval_info.invalid_evaluation_type')], 400);
        if($info->active != 1)
            throw HttpErrorsException::getInstance([__('errors.eval_info.inactive')], 400);

        $items = $info->getEvalItems();
        $item_ids = $items->pluck('id')->toArray();
        foreach($json['answers'] as $answer) {
            if(!$answer['item_id'] || !$answer['answer'] || !in_array($answer['item_id'], $item_ids))
                throw HttpErrorsException::getInstance([__('errors.eval_info.invalid_evaluation_type')], 400);
            $item_info = $this->_findInCollection($answer->item_id, $items);
            if(!$item_info)
                throw HttpErrorsException::getInstance([__('errors.eval_info.invalid_evaluation_type')], 400);
            if($item_info->type == EvaluationType::FIVE_STAR || $item_info->type == EvaluationType::SELECT) {
                $selectable_values = $item_info->getSelectAbleValues();
                if(!in_array($answer['answer'], $selectable_values))
                    throw HttpErrorsException::getInstance([__('errors.eval_info.invalid_evaluation_type')], 400);
                $this->eval_result += (int)$answer['answer'];
            }
        }

        $this->answers = $json->answers;
        $this->eval_info_id = $info->id;
    }

    /**
     * 컬렉션에서 지정 ID의 정보를 검색한다.
     * @param int $item_id
     * @param Collection $collection
     * @return EvalItem|null
     */
    private function _findInCollection(int $item_id, Collection $collection) : ?EvalItem {
        return $collection->find($item_id);
    }

    // Getter
    public function getEvalInfoId() : int {return $this->eval_info_id;}
    public function getAnswers() : array {return $this->answers;}
    public function getEvalResult() : int {return $this->eval_result;}

    // Creator
    /**
     * 요청 데이터로부터 DTO 객체를 리턴한다.
     * @param Request $request
     * @return EvaluationAnswerDto
     * @throws HttpException
     * @OA\Schema (
     *     schema="input_evaluation",
     *     title="평가결과 입력",
     *     @OA\Property (
     *         property="eval_info_id",
     *         type="integer",
     *         description="평가설문 마스트 일련번호"
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
     *     required={"eval_info_id", "answers"}
     * )
     */
    public static function createFromRequest(Request $request) : EvaluationAnswerDto {
        return new static($request->getContent());
    }

    // for model
    public function toArray() : array {
        return [
            'eval_info_id' => $this->eval_info_id,
            'answers' => json_encode($this->answers),
            'eval_result' => $this->eval_result
        ];
    }
}
