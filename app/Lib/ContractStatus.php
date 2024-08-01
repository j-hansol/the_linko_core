<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: "integer")]
enum ContractStatus : int {
    case REGISTERED             = 10;
    case PUBLISHED              = 20;
    case CONTRACT_PENDING       = 30;
    case CONTRACT_COMPLETED     = 40;
    case CONTRACT_CANCELED      = 50;
    case COMPANY_REGISTRATION   = 60;
    case COMPANY_FIXED          = 70;
    case WORKER_REVIEW          = 80;
    case WORKER_DECISION        = 90;
    case ATTORNEY_ASSIGN        = 100;
    case ATTORNEY_DECISION      = 110;
    case ENTRY_SCHEDULE         = 120;
    case ENTRY_DECISION         = 130;
    case INCOMING               = 140;
    case WORKING                = 150;
    case EVALUATION             = 160;
    case RETURN                 = 170;
    case END                    = 180;
}
