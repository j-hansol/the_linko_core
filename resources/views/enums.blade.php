<x-guest-layout>
    <x-slot:title>Enums for Api Documentation</x-slot:title>
<x-markdown class="markdown-body">
# 논리 데이터
```php
TRUE    = 1
FALSE   = 0
```

# 활동 지점 구분 (Action Point Type)
```php
#[Schema(type: "integer")]
enum ActionPointType : int {
    case WORK_PLACE     = 10;   // 근무지
    case RESIDENCE      = 20;   // 거주지
    case IN_WORK        = 30;   // 출근지점
    case OUT_WORK       = 40;   // 퇴근지점
    case ETC            = 90;   // 기타
}
```

# 배정된 근로자 상태 (Assigned Worker Status)
```php
#[Schema(type: "integer")]
enum AssignedWorkerStatus : int {
    case REGISTERED             = 10;   // 등록됨
    case REVIEW                 = 20;   // 심사 중
    case ENTRY_REJECT           = 30;   // 입국 거절
    case ENTRY_ALLOW            = 40;   // 입국 허락
    case ENTRY_SCHEDULE_FIXED   = 50;   // 입국일정 확정됨
    case ENTERED                = 60;   // 입국함
    case WORKING                = 70;   // 군무 중
    case EVALUATION             = 80;   // 평가 중
    case LEAVED                 = 90;   // 출국함
}
```

# 계약관련 파일 그룹 (Contract File Group)
```php
#[Schema(type: "integer")]
enum ContractFileGroup : int {
    case DOMESTIC_CERTIFICATION_DOCUMENT    = 10;
    case OVERSEA_CERTIFICATION_DOCUMENT     = 20;
    case CONTRACT_PROGRESS_DOCUMENT         = 30;
    case ETC                                = 90;
}
```

# 계약 상태 (Contract Status)
```php
#[Schema(type: "integer")]
enum ContractStatus : int {
    case REGISTERED             = 10;       // 등록됨
    case PUBLISHED              = 20;       // 공개됨
    case CONTRACT_PENDING       = 30;       // 보류됨
    case CONTRACT_COMPLETED     = 40;       // 계약완료됨
    case CONTRACT_CANCELED      = 50;       // 계약 취소됨
    case COMPANY_REGISTRATION   = 60;       // 기업 등록중
    case COMPANY_FIXED          = 70;       // 기업 확정됨
    case WORKER_REVIEW          = 80;       // 근로자 심사 중
    case WORKER_DECISION        = 90;       // 근로자 결정
    case ATTORNEY_ASSIGN        = 100;      // 행정사 배정 중
    case ATTORNEY_DECISION      = 110;      // 행정사 결정됨
    case ENTRY_SCHEDULE         = 120;      // 입국 일정 조정 중
    case ENTRY_DECISION         = 130;      // 입국 일정 확정됨
    case INCOMING               = 140;      // 입국
    case WORKING                = 150;      // 근무
    case EVALUATION             = 160;      // 평가
    case RETURN                 = 170;      // 귀국
    case END                    = 180;      // 사업 종료
}
```

# 계약 유형 (Contract Type)
```php
#[Schema(type: "integer")]
enum ContractType : int {
    case DIRECT         = 10;   // 직접 계약
    case INTERMEDIARY   = 20;   // 중개
}
```

# 단말기 유형 (Device Type)
```php
#[Schema(type: "integer")]
enum DeviceType : int {
    case TYPE_FIXED        = 10;    // 고정형
    case TYPE_MOBILE       = 20;    // 이동형 (모바일)
}
```

# 학력 수준 (Education Degree)
```php
#[Schema(type: "integer")]
enum EducationDegree : int {
    case MASTERS_DOCTORAL_DEGREE       = 30;        // 석/박사
    case BACHELORS_DEGREE              = 20;        // 대학
    case HIGH_SCHOOL_DIPLOMA           = 10;        // 고졸
    case OTHER                         = 990;       // 기타
}
```

# 평가 설문 유형 (Evaluation Type)
```php
#[Schema(type: "integer")]
enum EvaluationType : int {
    case FIVE_STAR      = 10;   // 5점형
    case SELECT         = 20;   // 선택형
    case WORD           = 30;   // 단답형
    case SENTENCE       = 40;   // 서술형
}
```

# 직업 구분 (Job Type)
```php
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
```

# 혼인 상태 (Marital Status)
```php
#[Schema(title: '혼인사항', type: 'integer')]
enum MaritalStatus : int {
    case MARRIED        = 10;   // 기혼
    case DIVORCED       = 20;   // 이혼
    case SINGLE         = 30;   // 싱글
}
```

# 회원 유형 (Member Type)
```php
#[Schema(type: "integer")]
enum MemberType: int {
    case TYPE_NONE                      = 10;   // 유형 미정
    case TYPE_OPERATOR                  = 20;   // 관리자
    case TYPE_INTERMEDIARY              = 30;   // 중개사
    case TYPE_GOVERNMENT                = 40;   // 지자체(국내정부)
    case TYPE_ORDER                     = 50;   // 발주기관(국내)
    case TYPE_COMPANY                   = 60;   // 기업(국내)
    case TYPE_PARTNER                   = 70;   // 협력사(국내)
    case TYPE_ATTORNEY                  = 80;   // 행정사(국내)
    case TYPE_MANAGER                   = 90;   // 관리기관(국내)
    case TYPE_MANAGER_OPERATOR          = 100;  // 관리기관 실무 요원(국내)
    case TYPE_PERSON                    = 110;  // 개인(국내)
    case TYPE_RECIPIENT                 = 120;  // 수주기관
    case TYPE_FOREIGN_GOVERNMENT        = 130;  // 지자체(해외)
    case TYPE_FOREIGN_PROVIDER          = 140;  // 근로자 공급기관(해외)
    case TYPE_FOREIGN_PARTNER           = 150;  // 협력사(해외)
    case TYPE_FOREIGN_MANAGER           = 160;  // 관리기관(해외)
    case TYPE_FOREIGN_MANAGER_OPERATOR  = 170;  // 관리기관 실무 요원(해외)
    case TYPE_FOREIGN_COMPANY           = 180;  // 기업(해외)
    case TYPE_FOREIGN_PERSON            = 190;  // 개인(해외)

    case TYPE_DEVELOPER                 = 900;  // 개발자
    case TYPE_MAINTAINER                = 910;  // 시스템 유집보수자
}
```

# 여권 구분 (Passport Type)
```php
#[Schema(type: "integer")]
enum PassportType: int {
    case TYPE_DIPLOMATIC    = 10;   // 외교관
    case TYPE_OFFICIAL      = 20;   // 관용
    case TYPE_REGULAR       = 30;   // 일반
    case TYPE_OTHER         = 990;  // 기타
}
```

# 비자 발급 상태 (Visa Application Status)
```php
#[Schema(type: "integer")]
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
}
```

# 회원 유형에 따른 설정 가능 비자발급 상태정보
```php
class VisaApplication extends Model {

    .....................

    /**
    * 회원 유형에 따라 설정 가능한 상태를 리턴한다.
    * @param MemberType $member_type
    * @return array
    */
    private static function _ableStatus(MemberType $member_type) : array {
        return match ($member_type) {
            MemberType::TYPE_ATTORNEY => [
                VisaApplicationStatus::STATUS_START_PREVIEW,
                VisaApplicationStatus::STATUS_ISSUE_IMPOSSIBLE,
                VisaApplicationStatus::STATUS_ISSUE_AVAILABLE,
                VisaApplicationStatus::STATUS_ISSUE_PREVIEW,
                VisaApplicationStatus::STATUS_ISSUE_APPLICATION,
                VisaApplicationStatus::STATUS_ISSUE_REJECT,
                VisaApplicationStatus::STATUS_ISSUE_COMPLETE
            ],
            MemberType::TYPE_FOREIGN_PERSON, MemberType::TYPE_FOREIGN_MANAGER => [
                VisaApplicationStatus::STATUS_REGISTERING,
                VisaApplicationStatus::STATUS_REGISTRATION_COMPLETE,
                VisaApplicationStatus::STATUS_ISSUE_PROCESS_REQUEST
            ],
            default => []
        };
    }

    /**
    * @param MemberType $member_type
    * @return bool
    */
    public static function isAbleStatus(MemberType $member_type) : bool {
        if (in_array($member_type, static::_ableStatus($member_type))) return true;
        return false;
    }
}
```

# 방문 목적 구분 (Visit Purpose)
```php
#[Schema(type: "integer")]
enum VisitPurpose : int {
    case TOURISM_TRANSIT                               = 10;    // 관광/통과
    case MEETING_CONFERENCE                            = 20;    // 행사 참석
    case MEDICAL_TOURISM                               = 30;    // 의료관광
    case BUSINESS_TRIP                                 = 40;    // 단기상용
    case STUDY_TRAINING                                = 50;    // 유학/연수
    case WORK                                          = 60;    // 취업활동
    case TRADE_INVESTMENT_INTRA_CORPORATE_TRANSFEREE   = 70;    // 무약/투자/주재
    case VISITING_FAMILY_RELATIVES_FRIENDS             = 80;    // 가족 또는 친지방문
    case MARRIAGE_MIGRANT                              = 90;    // 결혼이민
    case DIPLOMATIC_OFFICIAL                           = 100;   // 외교/공무
    case OTHER                                         = 990;   // 기타
}
```

# 컨설팅 권환 요청 처리 상태 구분 (Request Consulting Permission Status)
```php
#[Schema(type: "integer")]
enum RequestConsultingPermissionStatus : int {
    case REQUESTED  = 10;   // 요청됨
    case REJECTED   = 20;   // 반려됨 (다른 행정사가 선점함)
    case CONFIRMED  = 30;   // 수락됨
}
```

# 배정 근로자 상태정보
```php
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
}
```

# 평가 대상
```php
#[Schema(type: "integer")]
enum EvalTarget : int {
    case TARGET_WORKER  = 10;   // 평가 대상 : 근로자
    case TARGET_COMPANY = 20;   // 평가 대상 : 기업
}
```
</x-markdown>
</x-guest-layout>
