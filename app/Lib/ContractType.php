<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(description: '계약 유형', type: "integer")]
enum ContractType : int {
    case DIRECT         = 10;
    case INTERMEDIARY   = 20;
}
