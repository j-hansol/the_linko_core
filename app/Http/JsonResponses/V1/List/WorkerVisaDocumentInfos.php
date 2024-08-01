<?php

namespace App\Http\JsonResponses\V1\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Http\JsonResponses\V1\Base\WorkerVisaDocumentInfo;
use App\Lib\PageResult;
use OpenApi\Annotations as OA;

class WorkerVisaDocumentInfos extends PageResponse {
    /**
     * @param PageResult $result
     * @OA\Schema(
     *      schema="worker_visa_documents",
     *      title="근로자의 비자신청을 위한 문서 목록을 출력한다.",
     *      @OA\Property(
     *           property="items",
     *           type="array",
     *           description="방문 국가정보 목록",
     *           @OA\Items(
     *               type="object",
     *               allOf={@OA\Schema(ref="#/components/schemas/worker_visa_document")}
     *           )
     *      )
     *  )
     * /
     */
    function __construct(PageResult $result) {
        $data = [];
        foreach($result->collection as $document) $data[] = WorkerVisaDocumentInfo::toArray($document);
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
