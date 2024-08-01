<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(title: '근로자 추천 요청 승인 상태', type: "integer")]
enum WorkerRecommendationRequestStatus : int {
    case REGISTERED     = 10;
    case REJECTED       = 20;
    case ACCEPTED       = 30;
}
