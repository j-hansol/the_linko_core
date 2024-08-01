<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: "integer")]
enum QuestionType : int {
    case TYPE_FIVE_START   = 10;
    case TYPE_TEXT         = 20;
    case TYPE_LONG_TEXT    = 30;
}
