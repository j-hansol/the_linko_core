<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: "integer")]
enum EducationDegree : int {
    case VOCATIONAL_COURSE             = 40;
    case MASTERS_DOCTORAL_DEGREE       = 30;
    case BACHELORS_DEGREE              = 20;
    case HIGH_SCHOOL_DIPLOMA           = 10;
    case OTHER                         = 990;
}
