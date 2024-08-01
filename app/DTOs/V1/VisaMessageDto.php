<?php

namespace App\DTOs\V1;

use Illuminate\Http\Request;

class VisaMessageDto {
    // Getter
    public function getTitle() : string {return $this->title;}
    public function getMessage() : string {return $this->message;}

    // Creator
    function __construct(
        private readonly string $title,
        private readonly string $message
    ) {}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return VisaMessageDto
     */
    public static function createFromRequest(Request $request) : VisaMessageDto {
        return new static(
            $request->input('title'),
            $request->input('message')
        );
    }

    // for model
    public function toArray() : array {
        return [
            'title' => $this->title,
            'message' => $this->message,
        ];
    }
}
