<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: 'integer')]
enum WorkerActionPointSetReason : int {
    case NO_ACTION_POINT        = 10;   // 활동지점 미등록
    case DIFFERENT_POINT        = 20;   // 활동지점 사실과 다름
    case ETC                    = 990;  // 기타
}
