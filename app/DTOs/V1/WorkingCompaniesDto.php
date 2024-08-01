<?php

namespace App\DTOs\V1;

use Illuminate\Http\Request;

class WorkingCompaniesDto {
    private array $ids;

    // 생성자
    function __construct(
        private readonly string $working_company_ids,
        private readonly int $planned_worker_count
    ) {$this->ids = explode(',', $this->working_company_ids);}

    // Getter
    public function getWorkingCompanyIds() : array {return $this->ids;}
    public function getPlannedWorkerCount() : int {return $this->planned_worker_count;}

    // Creator
    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return WorkingCompaniesDto
     */
    public static function createFromRequest(Request $request) : WorkingCompaniesDto {
        return new static(
            $request->input('working_company_ids'),
            $request->integer('planned_worker_count')
        );
    }
}
