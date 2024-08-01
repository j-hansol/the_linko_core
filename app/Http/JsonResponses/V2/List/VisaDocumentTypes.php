<?php

namespace App\Http\JsonResponses\V2\List;

use App\Http\JsonResponses\Common\PageResponse;
use App\Lib\PageResult;
use App\Models\VisaDocumentType;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="visa_document_types",
 *     description="비자발급 시 필요한 문서 유형 목록",
 *     @OA\Property(
 *          property="items",
 *          type="array",
 *          @OA\Items(ref="#/components/schemas/visa_document_type")
 *     )
 * )
 */
class VisaDocumentTypes extends PageResponse {
    function __construct(PageResult $result) {
        $data = [];
        foreach ($result->collection as $t)
            if($t instanceof VisaDocumentType) $data[] = $t->toArray();
        parent::__construct($data, $result->param, $result->total, $result->total_page);
    }
}
