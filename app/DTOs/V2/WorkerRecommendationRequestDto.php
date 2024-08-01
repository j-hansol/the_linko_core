<?php

namespace App\DTOs\V2;

use Illuminate\Http\Request;

class WorkerRecommendationRequestDto {
    // 생성자
    function __construct(
        private readonly int $occupational_group_id,
        private readonly string $title,
        private readonly string $body,
        private readonly int $worker_count
    ) {}

    public function getOccupationalGroupId() : int {return $this->occupational_group_id;}
    public function getTitle() : string {return $this->title;}
    public function getBody() : string {return $this->body;}
    public function getWorkerCount() : int {return $this->worker_count;}

    // Creator
    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return WorkerRecommendationRequestDto
     */
    public static function createFromRequest(Request $request) : WorkerRecommendationRequestDto {
        return new static(
            $request->integer('occupational_group_id'),
            $request->input('title'),
            $request->input('body'),
            $request->integer('worker_count')
        );
    }

    // for model
    public function toArray() : array {
        return [
            'occupational_group_id' => $this->occupational_group_id,
            'title' => $this->title,
            'body' => $this->body,
            'worker_count' => $this->worker_count
        ];
    }
}
