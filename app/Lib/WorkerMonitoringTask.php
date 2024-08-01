<?php

namespace App\Lib;
use OpenApi\Attributes\Schema;

#[Schema(type: 'integer')]
enum WorkerMonitoringTask : int {
    case INVESTIGATION_ACTION_POINT         = 10;   // 활동지점 실태 조사
    case INVESTIGATION_ILLEGAL_ACTIVITY     = 20;   // 불법행위 실태 조사
    case INVESTIGATION_SALARY               = 30;   // 임금관련 실태 조사
    case INVESTIGATION_WELFARE              = 40;   // 복지관련 실태 조사
    case INVESTIGATION_DISASTER             = 50;   // 재해관련 실태 조사
    case INVESTIGATION_ENVIRONMENT          = 60;   // 근무환경 실태 조사
    case INVESTIGATION_WORKER_REPORT        = 70;   // 곤로자 신고 처리 및 조사
    case INVESTIGATION_ETC                  = 990;  // 기타 실태 조사
}
