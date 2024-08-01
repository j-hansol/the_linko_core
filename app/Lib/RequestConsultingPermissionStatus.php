<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: "integer")]
enum RequestConsultingPermissionStatus : int {
    case REQUESTED  = 10;   // 요청됨
    case REJECTED   = 20;   // 반려됨 (다른 행정사가 선점함)
    case CONFIRMED  = 30;   // 수락됨
}
