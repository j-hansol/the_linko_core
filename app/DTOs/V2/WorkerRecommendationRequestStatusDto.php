<?php

namespace App\DTOs\V2;

use App\Lib\WorkerRecommendationRequestStatus;
use Illuminate\Http\Request;

class WorkerRecommendationRequestStatusDto {
    // 생성자
    function __construct(readonly private int $status) {}

    // Getter
    public function getStatus() : int {return $this->status;}

    // Creator
    public static function createFromRequest(Request $request) : static {
        return new static($request->enum('status', WorkerRecommendationRequestStatus::class)->value);
    }
}
