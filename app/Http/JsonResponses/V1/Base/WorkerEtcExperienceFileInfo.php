<?php

namespace App\Http\JsonResponses\V1\Base;

use App\Models\User;
use App\Models\WorkerEtcExperienceFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

class WorkerEtcExperienceFileInfo extends JsonResponse {
    public function __construct(WorkerEtcExperienceFile $resume) {
        parent::__construct(static::toArray($resume));
    }

    /**
     * @param WorkerEtcExperienceFile $file
     * @return array
     * @OA\Schema (
     *     schema="worker_etc_experience_file",
     *     title="기타 경령 증비 서류",
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
    public static function toArray(WorkerEtcExperienceFile $file) : array {
        return [
            'id' => $file->id,
            'user' => User::findMe($file->user_id)->toSimpleArray(),
            'writer' => User::findMe($file->write_user_id)->toSimpleArray(),
            'is_image' => $file->file_path && is_web_image(Storage::mimeType($file->file_path)),
            'origin_name' => $file->file_name,
            'file_url' => $file->file_name ? route('api.v1.worker.show_etc_experience_file', ['id' => $file->id, '_token' => access_token()]) : null,
            'created_at' => $file->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $file->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
