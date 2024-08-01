<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema]
enum CertificationTokenFunction : string {
    case resetPassword     = 'resetPassword';
    case createPassword    = 'createPassword';
}
