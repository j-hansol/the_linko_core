<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(title: '요청 업무 유형', type: "integer")]
enum OrderedTaskType : int {
    case FACT_CHECK             = 10;
    case FACT_FINDING           = 20;
    case COMPLAIN_PROCESSING    = 30;
    case SELF_REPORT            = 40;
    case OTHER                  = 990;
}
