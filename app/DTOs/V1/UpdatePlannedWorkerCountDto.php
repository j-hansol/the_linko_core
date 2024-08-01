<?php

namespace App\DTOs\V1;

use Illuminate\Http\Request;

class UpdatePlannedWorkerCountDto {
    private array $ids;

    // 생성자
    function __construct(
        private readonly string $input_ids,
        private readonly int $planned_worker_count
    ) {$this->ids = explode(',', $this->input_ids);}

    // Getter
    public function getIds() : array {return $this->ids;}
    public function getPlannedWorkerCount() : int {return $this->planned_worker_count;}

    // Creator
    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return UpdatePlannedWorkerCountDto
     */
    public static function createFromRequest(Request $request) : UpdatePlannedWorkerCountDto {
        return new static(
            $request->input('ids'),
            $request->integer('planned_worker_count')
        );
    }
}
