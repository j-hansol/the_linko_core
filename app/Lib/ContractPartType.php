<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: 'integer')]
enum ContractPartType : int {
    case NONE               = 0;
    case ORDER              = 10;
    case RECIPIENT          = 20;
    case MEDIATION          = 30;
    case ORDER_MANAGER      = 40;
    case RECIPIENT_MANAGER  = 50;
}
