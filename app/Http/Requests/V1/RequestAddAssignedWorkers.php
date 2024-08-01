<?php

namespace App\Http\Requests\V1;

use App\Rules\ExistsValues;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestAddAssignedWorkers extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *      schema="worker_ids",
     *      title="채용 대상 근로자 계정 일련번호 목록",
     *      @OA\Property (
     *           property="worker_ids",
     *           type="array",
     *           @OA\Items (type="integer"),
     *           description="근로자 계정 일련번호 목록"
     *      )
     *  )
     */
    public function rules(): array {
        return [
            'worker_ids' => ['required', new ExistsValues('users', 'id')]
        ];
    }
}
