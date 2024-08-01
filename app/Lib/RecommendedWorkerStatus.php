<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(title: '근로자 공유 상태 정보', type: 'integer')]
enum RecommendedWorkerStatus : int {
    case RECOMMENDED    = 10;
    case UNDER_REVIEW   = 20;
    case SELECTED       = 30;
    case REJECTED       = 40;
    case EMPLOYED       = 50;
}
