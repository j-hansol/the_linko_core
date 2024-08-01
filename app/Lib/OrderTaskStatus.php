<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(title: '업무 처리 상태', type: 'integer')]
enum OrderTaskStatus : int {
    case ORDERED        = 10;
    case PROCESSING     = 20;
    case REPORTED       = 30;
    case REJECTED       = 40;
    case COMPLETED      = 50;
}
