<?php

namespace App\Http\JsonResponses\V1\List;

use App\Http\JsonResponses\Common\Data;
use Illuminate\Database\Eloquent\Collection;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema (
 *     schema="contract_managers",
 *     title="계약 관리 기관 목록",
 *     @OA\Property (
 *          type="array",
 *          @OA\Items (type="object", ref="#/components/schemas/simple_user_info")
 *     )
 * )
 */
class ContractManagers extends Data {
    function __construct(Collection $collection) {
        $data = [];
        foreach ($collection as $t) $data[] = $t->toInfoArray();
        parent::__construct($data);
    }
}
