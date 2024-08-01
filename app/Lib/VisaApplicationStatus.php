<?php

namespace App\Lib;

use App\Models\VisaApplication;
use OpenApi\Attributes\Schema;

#[Schema(description: '비자발급 진행 상태', type: "integer")]
enum VisaApplicationStatus : int {
    // 등록 단계
    case STATUS_REGISTERING             = 110;  // 등록 중
    case STATUS_REGISTRATION_COMPLETE   = 120;  // 등록 완료

    // 예비 심사 단계
    case STATUS_START_PREVIEW           = 210;  // 예비 심사 시작
    case STATUS_REQUEST_IMPROVEMENT     = 220;  // 개선 요청
    case STATUS_ISSUE_IMPOSSIBLE        = 230;  // 발급 불가
    case STATUS_ISSUE_AVAILABLE         = 240;  // 발급 가능

    // 발급 진행 요청
    case STATUS_ISSUE_PROCESS_REQUEST   = 310;  // 발급 절차 진행 요청

    // 발급 단계
    case STATUS_ISSUE_PREVIEW           = 410;  // 비자 신청전 검토
    case STATUS_ISSUE_APPLICATION       = 420;  // 비자 신청
    case STATUS_ISSUE_REJECT            = 430;  // 발급 거부
    case STATUS_ISSUE_COMPLETE          = 440;  // 발급 완료

    /**
     * 상태별 다음 가능 상태를 리턴한다.
     * @param VisaApplicationStatus $status
     * @return array|VisaApplicationStatus[]
     */
    public static function getWorkflow(VisaApplicationStatus $status) : array {
        return match ($status) {
            VisaApplicationStatus::STATUS_REGISTERING => [
                VisaApplicationStatus::STATUS_REGISTERING,
                VisaApplicationStatus::STATUS_REGISTRATION_COMPLETE
            ],
            VisaApplicationStatus::STATUS_REGISTRATION_COMPLETE => [
                VisaApplicationStatus::STATUS_REGISTRATION_COMPLETE,
                VisaApplicationStatus::STATUS_START_PREVIEW,
            ],
            VisaApplicationStatus::STATUS_START_PREVIEW => [
                VisaApplicationStatus::STATUS_START_PREVIEW,
                VisaApplicationStatus::STATUS_REQUEST_IMPROVEMENT,
                VisaApplicationStatus::STATUS_ISSUE_IMPOSSIBLE,
                VisaApplicationStatus::STATUS_ISSUE_AVAILABLE,
            ],
            VisaApplicationStatus::STATUS_REQUEST_IMPROVEMENT => [
                VisaApplicationStatus::STATUS_REQUEST_IMPROVEMENT,
                VisaApplicationStatus::STATUS_ISSUE_IMPOSSIBLE,
                VisaApplicationStatus::STATUS_ISSUE_AVAILABLE,
            ],
            VisaApplicationStatus::STATUS_ISSUE_AVAILABLE => [
                VisaApplicationStatus::STATUS_ISSUE_AVAILABLE,
                VisaApplicationStatus::STATUS_ISSUE_PROCESS_REQUEST,
            ],
            VisaApplicationStatus::STATUS_ISSUE_PROCESS_REQUEST => [
                VisaApplicationStatus::STATUS_ISSUE_PROCESS_REQUEST,
                VisaApplicationStatus::STATUS_ISSUE_PREVIEW,
                VisaApplicationStatus::STATUS_ISSUE_APPLICATION,
            ],
            VisaApplicationStatus::STATUS_ISSUE_PREVIEW => [
                VisaApplicationStatus::STATUS_ISSUE_PREVIEW,
                VisaApplicationStatus::STATUS_ISSUE_APPLICATION,
            ],
            VisaApplicationStatus::STATUS_ISSUE_APPLICATION => [
                VisaApplicationStatus::STATUS_ISSUE_APPLICATION,
                VisaApplicationStatus::STATUS_ISSUE_IMPOSSIBLE,
                VisaApplicationStatus::STATUS_ISSUE_COMPLETE,
            ],
            default => []
        };
    }

    /**
     * 다음 상태가 올바른지 여부를 판단한다.
     * @param VisaApplicationStatus $status
     * @return bool
     */
    public function isValidWorkflow(VisaApplicationStatus $status) : bool {
        return in_array($status, VisaApplicationStatus::getWorkflow($this));
    }

    /**
     * 변경가능 상태 여부를 판단한다.
     * @return bool
     */
    public function isUpdateAble() : bool {
        return match ($this) {
            VisaApplicationStatus::STATUS_ISSUE_APPLICATION, VisaApplicationStatus::STATUS_ISSUE_COMPLETE => false,
            default => true
        };
    }
    /**
     * 삭제 가능 상태 여부를 판단한다.
     * @return bool
     */
    public function isDeleteAble() : bool {
        return match ($this) {
            VisaApplicationStatus::STATUS_REGISTERING, VisaApplicationStatus::STATUS_REGISTRATION_COMPLETE => true,
            default => false
        };
    }
}
