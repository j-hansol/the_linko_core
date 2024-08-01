<?php

namespace App\DTOs\V2;

use App\Lib\EvaluationType;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use Illuminate\Http\Request;

class EvalItemDto {
    // 생성자
    /**
     * @param EvaluationType $type
     * @param string $question
     * @param string|null $answers
     * @throws HttpErrorsException
     */
    function __construct(
        private readonly EvaluationType $type,
        private readonly string $question,
        private readonly ?string $answers
    ) {
        if($this->type == EvaluationType::FIVE_STAR && !$this->_checkFiveStar($this->answers))
            throw HttpErrorsException::getInstance([__('errors.eval_info.invalid_evaluation_type')], 400);
        elseif($this->type == EvaluationType::SELECT && $this->_checkSelection($this->answers))
            throw HttpErrorsException::getInstance([__('errors.eval_info.invalid_evaluation_type')], 400);
    }

    // Getter
    public function getType() : EvaluationType {return $this->type;}
    public function getQuestion() : string {return $this->question;}
    public function getAnswer() : string {return $this->answers;}

    // Creator
    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return EvalItemDto
     * @throws HttpException
     */
    public static function createFromRequest(Request $request) : EvalItemDto {
        return new static(
            $request->enum('type', EvaluationType::class),
            $request->input('question'),
            $request->input('answers')
        );
    }

    // for model
    public function toArray() : array {
        return [
            'type' => $this->type->value,
            'question' => $this->question,
            'answers' => $this->answers
        ];
    }

    /**
     * 5점형 응답 패턴의 유효성을 검사한다.
     * @param string $answer
     * @return bool
     */
    private function _checkFiveStar(string $answer) : bool {
        $t = explode(';', $answer);
        if(count($t) != 5) return false;

        $t2 = [];
        foreach($t as $v) {
            list($name, $val) = explode(':', $v);
            $t2[(int)$val] = $name;
        }

        for($i = 1; $i <= 5; $i++) {
            if(empty($t2[$i])) return false;
        }

        return true;
    }

    /**
     * 선태형 응답 패턴의 유효성을 검사한다.
     * @param string $answer
     * @return bool
     */
    public function _checkSelection(string $answer) : bool {
        $t = explode(';', $answer);
        if(count($t) != 5) return false;

        $t2 = [];
        foreach($t as $v) {
            list($name, $val) = explode(':', $v);
            if(!$name || !$val) return false;
        }

        return true;
    }
}
