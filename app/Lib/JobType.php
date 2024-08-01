<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: "integer")]
enum JobType : int {
    case ENTREPRENEUR       = 10;       // 사업가
    case SELF_EMPLOYED      = 20;       // 자영업자
    case EMPLOYED           = 30;       // 직장인
    case CIVIL_SERVANT      = 40;       // 공무원
    case STUDENT            = 50;       // 학생
    case RETIRED            = 60;       // 퇴직자
    case UNEMPLOYED         = 70;       // 무직자
    case OTHER              = 990;      // 기타
}
