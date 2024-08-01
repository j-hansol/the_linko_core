<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: "integer")]
enum EvalTarget : int {
    case TARGET_WORKER  = 10;   // 평가 대상 : 근로자
    case TARGET_COMPANY = 20;   // 평가 대상 : 기업
}
