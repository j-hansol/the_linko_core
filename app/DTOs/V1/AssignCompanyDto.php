<?php

namespace App\DTOs\V1;

use App\Models\User;
use Illuminate\Http\Request;

class AssignCompanyDto {
    private array $ids;

    // 생성자
    function __construct(
        private readonly string $assigned_worker_ids,
        private readonly User $company
    ) {$this->ids = explode(',', $this->assigned_worker_ids);}

    // Getter
    public function getAssignedWorkerIds() : array {return $this->ids;}
    public function getCompany() : User {return $this->company;}
    public function getWorkerCount() : int {return count($this->ids);}

    // Creator
    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return AssignCompanyDto
     */
    public static function createFromRequest(Request $request) : AssignCompanyDto {
        return new static(
            $request->input('ids'),
            User::findMe($request->integer('company_user_id'))
        );
    }

}
