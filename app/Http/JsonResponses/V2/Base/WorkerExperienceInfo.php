<?php

namespace App\Http\JsonResponses\V2\Base;

use App\Models\User;
use App\Models\WorkerExperience;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

class WorkerExperienceInfo extends JsonResponse {
    function __construct(WorkerExperience $experience) {
        parent::__construct(static::toArray($experience));
    }

    /**
     * @param WorkerExperience $experience
     * @return array
     * @OA\Schema (
     *     schema="worker_experience_info",
     *     title="근로자 경력정보",
     *     @OA\Property(property="id", type="integer", description="일련번호"),
     *     @OA\Property(property="user", ref="#/components/schemas/simple_user_info"),
     *     @OA\Property(property="writer", ref="#/components/schemas/simple_user_info"),
     *     @OA\Property(property="company_name", type="string", description="근무 기업명"),
     *     @OA\Property(property="company_address", type="string", description="근무 기업 주소"),
     *     @OA\Property(property="task", type="string", description="업무"),
     *     @OA\Property(property="part", type="string", description="부서"),
     *     @OA\Property(property="position", type="string", description="직위/직급"),
     *     @OA\Property(property="job_description", type="string", description="업무 설명"),
     *     @OA\Property(property="start_date", type="string", format="date-time", description="근무 시작일"),
     *     @OA\Property(property="end_date", type="string", format="date-time", description="근무 종료일"),
     *     @OA\Property(property="is_image", type="boolean", description="이미자 파일 여부"),
     *     @OA\Property(property="origin_name", type="string", description="원본 파일명"),
     *     @OA\Property(property="file_url", type="string", description="이력서 파일 URL"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="등록일시"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", description="변경일시")
     * )
     */
    public static function toArray(WorkerExperience $experience) : array {
        return [
            'id' => $experience->id,
            'user' => User::findMe($experience->user_id)->toSimpleArray(),
            'writer' => User::findMe($experience->write_user_id)->toSimpleArray(),
            'company_name' => $experience->company_name,
            'company_address' => $experience->company_address,
            'task' => $experience->task,
            'part' => $experience->part,
            'position' => $experience->position,
            'job_description' => $experience->job_description,
            'start_date' => $experience->start_date,
            'end_date' => $experience->end_date,
            'is_image' => $experience->file_path && is_web_image(get_mime_type('local', $experience->file_path)),
            'origin_name' => $experience->file_name,
            'file_url' => $experience->file_path ? route('api.v1.worker.show_experience_file', ['id' => $experience->id, '_token' => access_token()]) : null,
            'created_at' => $experience->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $experience->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
