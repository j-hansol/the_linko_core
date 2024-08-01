<?php

namespace App\Http\Requests\V1;

use App\Lib\ActionPointType;
use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class RequestActionPoint extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="input_action_point",
     *     title="기입 기본 활동지점 입력",
     *     @OA\Property (
     *         property="type",
     *         ref="#/components/schemas/ActionPointType",
     *         description="활동 지점 유형"
     *     ),
     *     @OA\Property (
     *         property="name",
     *         type="string",
     *         description="활동 지점 이름"
     *     ),
     *     @OA\Property (
     *         property="address",
     *         type="string",
     *         description="주소"
     *     ),
     *     @OA\Property (
     *         property="longitude",
     *         type="number",
     *         format="double",
     *         description="경도"
     *     ),
     *     @OA\Property (
     *         property="latitude",
     *         type="number",
     *         format="double",
     *         description="업무 소개 영상 (mp4만 가능, 30MB를 초과할 수 없음)"
     *     ),
     *     @OA\Property (
     *         property="radius",
     *         type="number",
     *         format="double",
     *         description="활돟 반경(킬로미터 단위) 최소 0.1km"
     *     ),
     *     required={"type","name","address","longitude","latitude","radius"}
     * )
     */
    public function rules(): array {
        return [
            'type' => ['required', new Enum(ActionPointType::class)],
            'name' => ['required', 'string'],
            'address' => ['required', 'string'],
            'longitude' => ['required', 'numeric'],
            'latitude' => ['required', 'numeric'],
            'radius' => ['required', 'numeric', 'min:0.1']
        ];
    }
}
