<?php

namespace App\Http\JsonResponses\V1\Base;

use App\Models\User;
use App\Models\WorkerEducation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

class WorkerEducationInfo extends JsonResponse {
    function __construct(WorkerEducation $education) {
        parent::__construct(static::toArray($education));
    }

    /**
     * @param WorkerEducation $education
     * @return array
     * @OA\Schema(
     *     schema="worker_education_info",
     *     title="근로자 학력정보",
     *     @OA\Property(property="id", type="number", description="일련번호"),
     *     @OA\Property(property="user", ref="#/components/schemas/simple_user_info"),
     *     @OA\Property(property="writer", ref="#/components/schemas/simple_user_info"),
     *     @OA\Property(property="degree", type="number", description="학력구분"),
     *     @OA\Property(property="school_name", type="string", description="학교/기관명"),
     *     @OA\Property(property="course_name", type="string", description="과정명"),
     *     @OA\Property(property="start_year", type="number", description="수강 시작 년도"),
     *     @OA\Property(property="end_year", type="number", description="수강 종료 년도"),
     *     @OA\Property(property="origin_name", type="string", description="원본 파일명"),
     *     @OA\Property(property="is_image", type="boolean", description="이미지 파일 여부"),
     *     @OA\Property(property="file_url", type="string", description="파일 URL"),
     *     @OA\Property (property="created_at", type="string", format="date-time", description="등록일시"),
     *     @OA\Property (property="updated_at", type="string", format="date-time", description="변경일시")
     * )
     */
    public static function toArray(WorkerEducation $education) : array {
        return [
            'id' => $education->id,
            'user' => User::findMe($education->user_id)->toSimpleArray(),
            'writer' => User::findMe($education->write_user_id)->toSimpleArray(),
            'degree' => $education->degree,
            'school_name' => $education->school_name,
            'course_name' => $education->course_name,
            'start_year' => $education->start_year,
            'end_year' => $education->end_year,
            'origin_name' => $education->origin_name,
            'is_image' => $education->file_path && is_web_image(Storage::mimeType($education->file_path)),
            'file_url' => $education->file_path ? route('api.v1.worker.show_worker_education_file', ['id' => $education->id, '_token' => access_token()]) : null,
            'created_at' => $education->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $education->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
