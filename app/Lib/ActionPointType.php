<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: "integer")]
enum ActionPointType : int {
    case WORK_PLACE     = 10;
    case RESIDENCE      = 20;
    case IN_WORK        = 30;
    case OUT_WORK       = 40;
    case ETC            = 90;
}
