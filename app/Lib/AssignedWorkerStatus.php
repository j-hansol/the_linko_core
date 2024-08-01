<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: "integer")]
enum AssignedWorkerStatus : int {
    case REGISTERED             = 10;   // 등록됨
    case REVIEW                 = 20;   // 심사중
    case ENTRY_REJECT           = 30;   // 입국불허
    case ENTRY_ALLOW            = 40;   // 입국허가
    case ENTRY_SCHEDULE_FIXED   = 50;   // 입국일정 확정
    case ENTERED                = 60;   // 입국함
    case WORKING                = 70;   // 근무중
    case EVALUATION             = 80;   // 평가
    case LEAVED                 = 90;   // 출국

    /**
     * 상태에 따른 다음 이용 가능 상태정보를 리턴한다.
     * @param AssignedWorkerStatus $status
     * @return AssignedWorkerStatus[]
     */
    public static function getWorkFlow(AssignedWorkerStatus $status) : array {
        return match ($status) {
            AssignedWorkerStatus::REGISTERED => [AssignedWorkerStatus::REVIEW],
            AssignedWorkerStatus::REVIEW => [AssignedWorkerStatus::ENTRY_REJECT, AssignedWorkerStatus::ENTRY_ALLOW],
            AssignedWorkerStatus::ENTRY_ALLOW => [AssignedWorkerStatus::ENTRY_SCHEDULE_FIXED],
            AssignedWorkerStatus::ENTRY_SCHEDULE_FIXED => [AssignedWorkerStatus::ENTERED],
            AssignedWorkerStatus::ENTERED => [AssignedWorkerStatus::WORKING],
            default => [AssignedWorkerStatus::LEAVED]
        };
    }

    /**
     * 계약 구성원이 지정 상태를 설정 가능한지 여부를 판단한다.
     * @param ContractPartType $type
     * @param AssignedWorkerStatus $status
     * @return bool
     */
    public static function isAble(ContractPartType $type, AssignedWorkerStatus $status) : bool {
        return match ($type) {
            ContractPartType::ORDER_MANAGER => in_array($status, [
                    AssignedWorkerStatus::REVIEW, AssignedWorkerStatus::ENTRY_REJECT, AssignedWorkerStatus::ENTRY_ALLOW,
                    AssignedWorkerStatus::ENTERED, AssignedWorkerStatus::WORKING, AssignedWorkerStatus::EVALUATION,
                    AssignedWorkerStatus::LEAVED
                ]
            ),
            ContractPartType::RECIPIENT_MANAGER => $status == AssignedWorkerStatus::ENTRY_SCHEDULE_FIXED,
            default => false,
        };
    }
}
