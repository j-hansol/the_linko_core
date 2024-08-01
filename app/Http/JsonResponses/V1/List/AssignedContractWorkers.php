<?php

namespace App\Http\JsonResponses\V1\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Lib\PageResult;

class AssignedContractWorkers extends PageResponse {
    /**
     * 계약관련 취업 대상 근로자 목록으로 응답한다.
     * @param PageResult $result
     */
    function __construct(PageResult $result) {
        $data = [];
        foreach ($result->collection as $t) $data[] = $t->toWorkerInfoArray();
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
