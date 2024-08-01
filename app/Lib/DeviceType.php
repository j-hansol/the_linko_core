<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: "integer")]
enum DeviceType : int {
    case TYPE_FIXED        = 10;
    case TYPE_MOBILE       = 20;
}
