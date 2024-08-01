<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: "integer")]
enum VisaApplicationFileType : int {
    case TYPE_SKILL_CERTIFICATION          = 10;
    case TYPE_EDUCATION_CERTIFICATION      = 20;
    case TYPE_ESTATE_CERTIFICATION_        = 30;
    case TYPE_MEDICAL_CERTIFICATION        = 40;
    case TYPE_CRIMINAL_FACT_CERTIFICATE    = 50;
}
