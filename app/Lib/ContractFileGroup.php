<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: "integer")]
enum ContractFileGroup : int {
    case DOMESTIC_CERTIFICATION_DOCUMENT    = 10;   // 국내 인허가용 문서
    case OVERSEA_CERTIFICATION_DOCUMENT     = 20;   // 해외 인허가용 문서
    case CONTRACT_PROGRESS_DOCUMENT         = 30;   // 계약진행관련 문서
    case ETC                                = 90;   // 기타
}
