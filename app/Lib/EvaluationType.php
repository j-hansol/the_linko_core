<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: "integer")]
enum EvaluationType : int {
    case FIVE_STAR      = 10;
    case SELECT         = 20;
    case WORD           = 30;
    case SENTENCE       = 40;
}
