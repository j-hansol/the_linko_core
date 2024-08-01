<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: "integer")]
enum LoginMethod : int {
    case LOGIN_METHOD_SNS          = 10;  // SNS 로그인
    case LOGIN_METHOD_PASSWORD     = 20;  // 비밀번호 로그인
}
