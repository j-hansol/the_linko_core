<?php

namespace App\Http\JsonResponses\V1\Base;

use App\Models\VisaDocumentType;
use App\Models\WorkerVisaDocument;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class WorkerVisaDocumentInfo extends JsonResponse {
    function __construct(WorkerVisaDocument $document) {
        parent::__construct([
            'message' => __('api.r200')
        ] + static::toArray($document));
    }

    /**
     * 문서정보를 배열로 리턴한다.
     * @param WorkerVisaDocument $document
     * @return array
     * @OA\Schema(
     *     schema="worker_visa_document",
     *     title="비자발급시 필요한 문서",
     *     @OA\Property (
     *          property="id",
     *          type="integer",
     *          description="일련번호",
     *     ),
     *     @OA\Property (property="type",  ref="#/components/schemas/simple_visa_document_type"),
     *     @OA\Property (
     *          property="title",
     *          type="string",
     *          description="문서제목",
     *     ),
     *     @OA\Property (
     *          property="file",
     *          type="string",
     *          description="파일 저장 경로",
     *     ),
     *     @OA\Property (
     *          property="created_at",
     *          type="string",
     *          format="date-time",
     *          description="등록일시",
     *     ),
     *     @OA\Property (
     *          property="updated_at",
     *          type="string",
     *          format="date-time",
     *          description="변경일시",
     *     )
     * )
     */
    public static function toArray(WorkerVisaDocument $document) : array {
        return [
            'id' => $document->id,
            'type' => VisaDocumentType::findMe($document->type_id)->toSimpleArray(),
            'title' => $document->title,
            'file' => route('api.v1.worker.show_visa_document', ['id' => $document->id, '_token' => access_token()]),
            'created_at' => $document->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $document->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
