<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: "integer")]
enum PassportType: int {
    case TYPE_DIPLOMATIC    = 10;   // 외교관
    case TYPE_OFFICIAL      = 20;   // 관용
    case TYPE_REGULAR       = 30;   // 일반
    case TYPE_OTHER         = 990;  // 기타
}
