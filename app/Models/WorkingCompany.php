<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class WorkingCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id', 'company_user_id', 'planned_worker_count', 'assigned_worker_count'
    ];

    public $timestamps = false;

    /**
     * 출력을 위한 정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="working_company",
     *     description="근무 기업정보",
     *     @OA\Property(
     *          property="id",
     *          type="integer",
     *          description="일련번호"
     *     ),
     *     @OA\Property(
     *          property="contract_id",
     *          type="integer",
     *          description="계약 정보 일련번호"
     *     ),
     *     @OA\Property(
     *          property="company",
     *          type="object",
     *          description="근무기업정보",
     *          ref="#/components/schemas/simple_user_info"
     *     ),
     *     @OA\Property(
     *          property="planned_worker_count",
     *          type="integer",
     *          description="채용 계획 근로자 수"
     *     ),
     *     @OA\Property(
     *          property="assigned_worker_count",
     *          type="integer",
     *          description="채용된 근로자 수"
     *     )
     * )
     */
    public function toInfoArray() : array {
        return [
            'id' => $this->id,
            'contract_id' => $this->contract_id,
            'company' => User::findMe($this->company_user_id)?->toSimpleArray(),
            'planned_worker_count' => $this->planned_worker_count,
            'assigned_worker_count' => $this->assigned_worker_count
        ];
    }
}
