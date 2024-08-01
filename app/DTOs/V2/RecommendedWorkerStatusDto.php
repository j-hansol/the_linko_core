<?php

namespace App\DTOs\V2;

use Illuminate\Http\Request;

class RecommendedWorkerStatusDto {
    function __construct(private readonly int $status) {}

    // Getter
    public function getStatus() : int {return $this->status;}

    // Creator

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return static
     */
    public static function createFromRequest(Request $request) : static {
        return new static($request->input('status'));
    }
}
