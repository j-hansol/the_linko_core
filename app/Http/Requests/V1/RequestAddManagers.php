<?php

namespace App\Http\Requests\V1;

use App\Rules\ExistsValues;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestAddManagers extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *      schema="manager_user_ids",
     *      title="관리기관 일련번호 목록",
     *      @OA\Property (
     *          property="manager_user_ids",
     *          type="array",
     *          @OA\Items (type="integer"),
     *          description="관리기관 계정 일련번호"
     *     )
     * )
     */
    public function rules(): array {
        return ['manager_user_ids' => ['required', new ExistsValues('users', 'id')]];
    }
}
