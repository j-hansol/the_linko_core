<?php
namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(type: "integer")]
enum MemberType: int {
    case TYPE_ALL                       = -1;   // 전체 프로그램에서만 사용
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

    case TYPE_PREMIUM                   = 300;  // 유료회원
    case TYPE_PARTNERSHIP               = 310;  // 파트너

    case TYPE_DEVELOPER                 = 900;  // 개발자
    case TYPE_MAINTAINER                = 910;  // 시스템 유지보수자

    /**
     * 회원 유형이 해외 단체인지 여부를 판단하여 리턴한다.
     * @param int $value
     * @return bool
     */
    public static function isForeignOrganization(int $value) : bool {
        $t = self::tryFrom($value);
        if(!$t) return false;
        return match($t) {
            self::TYPE_FOREIGN_GOVERNMENT, self::TYPE_FOREIGN_PARTNER, self::TYPE_FOREIGN_PROVIDER,
            self::TYPE_FOREIGN_MANAGER, self::TYPE_FOREIGN_COMPANY => true,
            default => false
        };
    }

    /**
     * 현재 값을 기준으로 회원 유형이 해외 단체인지 여부플 판단한다.
     * @return bool
     */
    public function checkForeignOrganization() : bool {
        return MemberType::isForeignOrganization($this->value);
    }

    /**
     * 회원 유형이 해외 개인회원 여부를 판단하여 리턴한다.
     * @param int $value
     * @return bool
     */
    public static function isForeignPerson(int $value) : bool {
        $t = self::tryFrom($value);
        if(!$t) return false;
        return match($t) {
            self::TYPE_FOREIGN_MANAGER_OPERATOR, self::TYPE_FOREIGN_PERSON => true,
            default => false
        };
    }

    /**
     * 현재 값을 기준으로 회원 유형이 해외 개인 여부플 판단한다.
     * @return bool
     */
    public function checkForeignPerson() : bool {
        return MemberType::isForeignPerson($this->value);
    }

    /**
     * 회원 유형이 국내 단체인지 여부를 판단하여 리턴한다.
     * @param int $value
     * @return bool
     */
    public static function isKoreanOrganization( int $value ) : bool {
        $t = self::tryFrom($value);
        if(!$t) return false;
        return match($t) {
            self::TYPE_GOVERNMENT, self::TYPE_PARTNER, self::TYPE_COMPANY, self::TYPE_ATTORNEY, self::TYPE_MANAGER,
            self::TYPE_ORDER, self::TYPE_INTERMEDIARY => true,
            default => false
        };
    }

    /**
     * 현재 값을 기준으로 국내 단체인지 여부를 판단하여 리턴한다.
     * @return bool
     */
    public function checkKoreaOrganization() : bool {
        return MemberType::isKoreanOrganization($this->value);
    }

    /**
     * 회원 유형이 국내 개인 여부를 판단하여 리턴한다.
     * @param int $value
     * @return bool
     */
    public static function isKoreanPerson( int $value ) : bool {
        $t = self::tryFrom($value);
        if(!$t) return false;
        return match($t) {
            self::TYPE_OPERATOR, self::TYPE_PERSON, self::TYPE_MANAGER_OPERATOR => true,
            default => false
        };
    }

    /**
     * 현재 값을 기준으로 국내 개인 여부를 판단하여 리턴한다.
     * @return bool
     */
    public function checkKoreaPerson() : bool {
        return MemberType::isKoreanPerson($this->value);
    }

    /**
     * 계약가능 기관 여부를 판단한다.
     * @param int $value
     * @return bool
     */
    public static function isContractOrganization(int $value) : bool {
        $t = self::tryFrom($value);
        if(!$t) return false;
        return match($t) {
            self::TYPE_RECIPIENT, self::TYPE_ORDER => true,
            default => false
        };
    }

    /**
     * 현재 값을 기준으로 계약가능 기관 여부를 판단한다.
     * @return bool
     */
    public function checkContractOrganization() : bool {
        return MemberType::isContractOrganization($this->value);
    }

    /**
     * 단처 유형 여부를 판단한다.
     * @return bool
     */
    public function checkOrganization() : bool {
        return $this->checkKoreaOrganization() || $this->checkForeignOrganization() || $this->checkContractOrganization();
    }

    /**
     * 개인 유형 여부를 판단한다.
     * @return bool
     */
    public function checkPerson() : bool {
        return $this->checkKoreaPerson() || $this->checkForeignPerson();
    }

    /**
     * 회원 유형이 서비스 운영자, 관리기관 실무자 여부를 판단한다.
     * @param int $value
     * @return bool
     */
    public static function isOperator(int $value) : bool {
        $t = self::tryFrom($value);
        if(!$t) return false;
        return match($t) {
            self::TYPE_OPERATOR => true,
            default => false
        };
    }

    /**
     * 현재 값을 기준으로 서비스 운영자, 관리기관 실무자 여부를 판단한다.
     * @return bool
     */
    public function checkOperator() : bool {
        return MemberType::isOperator($this->value);
    }

    /**
     * 해외 근로자 관리기관 실무자 여부플 판단한다.
     * @param int $value
     * @return bool
     */
    public static function isForeignManagerOperator(int $value) : bool {
        $t = self::tryFrom($value);
        if(!$t) return false;
        return match($t) {
            self::TYPE_FOREIGN_MANAGER_OPERATOR => true,
            default => false
        };
    }

    /**
     * 현재 유형이 근로자 관리기관 실무자 여부를 판단한다.
     * @return bool
     */
    public function checkForeignManagerOperator() : bool {
        return MemberType::isForeignManagerOperator($this->value);
    }

    /**
     * 근로자 관리기관 실무자 여부플 판단한다.
     * @param int $value
     * @return bool
     */
    public static function isManagerOperator(int $value) : bool {
        $t = self::tryFrom($value);
        if(!$t) return false;
        return match($t) {
            self::TYPE_MANAGER_OPERATOR => true,
            default => false
        };
    }

    /**
     * 현재 유형이 근로자 관기 기관 유형인지 여부를 판단한다.
     * @return bool
     */
    public function checkManagerOperator() : bool {
        return MemberType::isManagerOperator($this->value);
    }

    /**
     * 지정 회원 유형의 접두사를 리턴한다.
     * @param int $value
     * @return string|null
     */
    public static function getPrefix(int $value) : ?string {
        $t = self::tryFrom($value);
        if(!$t) return null;
        return match ($t) {
            self::TYPE_NONE => 'TNN',
            self::TYPE_OPERATOR => 'OPR',
            self::TYPE_INTERMEDIARY => 'INM',
            self::TYPE_GOVERNMENT => 'KGO',
            self::TYPE_ORDER => 'KOD',
            self::TYPE_COMPANY => 'KCO',
            self::TYPE_PARTNER => 'KPA',
            self::TYPE_ATTORNEY => 'KAT',
            self::TYPE_MANAGER => 'KMA',
            self::TYPE_MANAGER_OPERATOR => 'KMO',
            self::TYPE_PERSON => 'KPE',
            self::TYPE_FOREIGN_GOVERNMENT => 'FGO',
            self::TYPE_FOREIGN_PROVIDER => 'FPR',
            self::TYPE_FOREIGN_PARTNER => 'FPA',
            self::TYPE_FOREIGN_MANAGER => 'FMA',
            self::TYPE_FOREIGN_MANAGER_OPERATOR => 'FMO',
            self::TYPE_FOREIGN_COMPANY => 'FCO',
            self::TYPE_FOREIGN_PERSON => 'FPE',
        };
    }

    /**
     * 단체 유형 값을 리턴한다.
     * @param string $group
     * @return array
     */
    public static function getOrganizationTypeValues(string $group = 'all') : array {
        if($group == 'korea') return [
            self::TYPE_GOVERNMENT->value, self::TYPE_PARTNER->value, self::TYPE_COMPANY->value,
            self::TYPE_ATTORNEY->value, self::TYPE_MANAGER->value, self::TYPE_ORDER->value, self::TYPE_INTERMEDIARY->value
        ];
        elseif($group == 'foreign') return [
            self::TYPE_FOREIGN_GOVERNMENT->value, self::TYPE_FOREIGN_PARTNER->value, self::TYPE_FOREIGN_PROVIDER->value,
            self::TYPE_FOREIGN_MANAGER->value, self::TYPE_FOREIGN_COMPANY->value, self::TYPE_RECIPIENT->value
        ];
        else return [
            self::TYPE_GOVERNMENT->value, self::TYPE_PARTNER->value, self::TYPE_COMPANY->value,
            self::TYPE_ATTORNEY->value, self::TYPE_MANAGER->value, self::TYPE_ORDER->value, self::TYPE_INTERMEDIARY->value,
            self::TYPE_FOREIGN_GOVERNMENT->value, self::TYPE_FOREIGN_PARTNER->value, self::TYPE_FOREIGN_PROVIDER->value,
            self::TYPE_FOREIGN_MANAGER->value, self::TYPE_FOREIGN_COMPANY->value, self::TYPE_RECIPIENT->value
        ];
    }

    /**
     * 근로자 관리 기관 실무자 유형 값을 리턴한다.
     * @return array
     */
    public static function getManagerOperatorValues() : array {
        return [
            self::TYPE_MANAGER_OPERATOR->value, self::TYPE_FOREIGN_MANAGER_OPERATOR->value
        ];
    }
}
