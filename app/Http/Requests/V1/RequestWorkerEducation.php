<?php

namespace App\Http\Requests\V1;

use App\Lib\EducationDegree;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestWorkerEducation extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="input_worker_education_info",
     *     title="근로자 학력정보 입력",
     *     @OA\Property(property="degree", ref="#/components/schemas/EducationDegree", description="학력구분"),
     *     @OA\Property(property="school_name", type="string", description="학교/기관명"),
     *     @OA\Property(property="course_name", type="string", description="과정명"),
     *     @OA\Property(property="start_year", type="integer", description="수강 시작 년도"),
     *     @OA\Property(property="end_date", type="integer", description="수강 종료 년도"),
     *     @OA\Property(property="file", type="string", format="binary", description="관련 파일")
     * )
     */
    public function rules(): array {
        return [
            'degree' => ['required', new Enum(EducationDegree::class)],
            'school_name' => ['required'],
            'start_year' => ['nullable', 'integer'],
            'end_year' => ['nullable', 'integer'],
            'file' => ['nullable', 'file']
        ];
    }
}
