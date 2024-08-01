<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(title: '혼인사항', type: 'integer')]
enum MaritalStatus : int {
    case MARRIED        = 10;
    case DIVORCED       = 20;
    case SINGLE         = 30;
}
