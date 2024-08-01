<?php

namespace App\Http\JsonResponses\V1\Base;

use App\Models\User;
use App\Models\WorkerResume;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

class WorkerResumeInfo extends JsonResponse {
    public function __construct(WorkerResume $resume) {
        parent::__construct(static::toArray($resume));
    }

    /**
     * @param WorkerResume $resume
     * @return array
     * @OA\Schema (
     *     schema="worker_resume_info",
     *     title="근로자 이력서 정보",
     *     @OA\Property(property="id", type="integer", description="일련번호"),
     *     @OA\Property(property="user", ref="#/components/schemas/simple_user_info"),
     *     @OA\Property(property="writer", ref="#/components/schemas/simple_user_info"),
     *     @OA\Property(property="is_image", type="boolean", description="이미자 파일 여부"),
     *     @OA\Property(property="origin_name", type="string", description="원본 파일명"),
     *     @OA\Property(property="file_url", type="string", description="이력서 파일 URL"),
     *     @OA\Property (property="created_at", type="string", format="date-time", description="등록일시"),
     *     @OA\Property (property="updated_at", type="string", format="date-time", description="변경일시")
     * )
     */
    public static function toArray(WorkerResume $resume) : array {
        return [
            'id' => $resume->id,
            'user' => User::findMe($resume->user_id)->toSimpleArray(),
            'writer' => User::findMe($resume->write_user_id)->toSimpleArray(),
            'is_image' => $resume->file_path && is_web_image(Storage::mimeType($resume->file_path)),
            'origin_name' => $resume->file_name,
            'file_url' => $resume->file_name ? route('api.v1.worker.show_resume_file', ['id' => $resume->id, '_token' => access_token()]) : null,
            'created_at' => $resume->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $resume->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
