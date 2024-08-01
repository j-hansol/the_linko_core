<?php

namespace App\DTOs\V2;

use App\Lib\EvalTarget;
use Illuminate\Http\Request;

class EvalInfoDto {
    // 속성
    private ?string $description;

    // 생성자
    function __construct(
        private readonly string $title,
        private readonly EvalTarget $target,
        private readonly bool $active
    ) {}

    // Setter, Getter
    public function getTitle() : string {return $this->title;}
    public function getEvalTarget() : EvalTarget {return $this->target;}
    public function setDescription(?string $description) : void {$this->description = $description;}
    public function getDescription() : ?string {return $this->description;}
    public function getActive() : bool {return $this->active;}

    // Creator

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return EvalInfoDto
     */
    public static function createFromRequest(Request $request) : EvalInfoDto {
        $dto = new static(
            $request->input('title'),
            $request->enum('target', EvalTarget::class),
            $request->boolean('active')
        );
        $dto->setDescription($request->input('description'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'title' => $this->title,
            'target' => $this->target->value,
            'description' => $this->description,
            'active' => $this->active ? 1 : 0
        ];
    }
}
