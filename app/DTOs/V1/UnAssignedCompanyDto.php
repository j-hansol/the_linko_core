<?php

namespace App\DTOs\V1;

use Illuminate\Http\Request;

class UnAssignedCompanyDto {
    private array $ids;

    // 생성자
    function __construct(private readonly string $assigned_worker_ids) {
        $this->ids = explode(',', $this->assigned_worker_ids);
    }

    // Getter
    public function getAssignedWorkerIds() : array {return $this->ids;}

    // Creator
    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return UnAssignedCompanyDto
     */
    public static function createFromRequest(Request $request) : UnAssignedCompanyDto {
        return new static($request->input('assigned_worker_ids'));
    }
}
