<?php

namespace App\Http\Requests\V1;

use App\Traits\Common\RequestValidation;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

class RequestConsultingMessage extends FormRequest {
    use RequestValidation;

    public function authorize(): bool {return true;}

    /**
     * 유효성 검사 오류 시 JsonResponse 객체를 리턴하도록 한다.
     * @return array
     * @OA\Schema (
     *     schema="send_consulting_message_to_worker",
     *     title="컨설팅 메시지 전송",
     *     @OA\Property(
     *          property="title",
     *          type="string",
     *          description="제목"
     *     ),
     *     @OA\Property(
     *          property="message",
     *          type="string",
     *          description="메시지"
     *     ),
     *     required={"title","message"}
     * )
     */
    public function rules(): array {
        return [
            'title' => ['required'],
            'message' => ['required'],
        ];
    }
}
