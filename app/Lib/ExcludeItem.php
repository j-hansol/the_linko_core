<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

#[Schema(title: '공개 제외 대상 정보', type: 'string')]
enum ExcludeItem : string {
    case IDENTIFIER = 'IDENTIFIER';
    case IDENTIFIER_NO = 'IDENTIFIER_NO';
    case PERSON_NAME = 'PERSON_NAME';
    case GENDER = 'GENDER';
    case PHOTO = 'PHOTO';
    case ADDRESS = 'ADDRESS';
    case PHONE_NUMBER = 'PHONE_NUMBER';
    case EMAIL = 'EMAIL';
    case BIRTHDAY = 'BIRTHDAY';
    case COUNTRY = 'COUNTRY';
    case MANAGER = 'MANAGER';
    case SPOUSE_NAME = 'SPOUSE_NAME';
    case CHILDREN_NAME = 'CHILDREN_NAME';
    case CERTIFICATION_FILE = 'CERTIFICATION_FILE';

    /**
     * 각 항목의 제목을 리턴한다.
     * @param ExcludeItem $item
     * @return string|null
     */
    static function getTitle(ExcludeItem $item) : ?string {
        return match ($item) {
            ExcludeItem::IDENTIFIER => '식별자(ID, ID 별칭)',
            ExcludeItem::IDENTIFIER_NO => '신분증/사업자/법인 번호',
            ExcludeItem::PERSON_NAME => '개인 이름',
            ExcludeItem::GENDER => '성별',
            ExcludeItem::PHOTO => '사진',
            ExcludeItem::ADDRESS => '주소',
            ExcludeItem::PHONE_NUMBER => '전화번호(팩스, 휴대전화 포함)',
            ExcludeItem::EMAIL => '전자우편 주소',
            ExcludeItem::BIRTHDAY => '생년월일',
            ExcludeItem::COUNTRY => '국가정보',
            ExcludeItem::MANAGER => '관리 담당자',
            ExcludeItem::SPOUSE_NAME => '배우자 이름',
            ExcludeItem::CHILDREN_NAME => '자녀 이름',
            ExcludeItem::CERTIFICATION_FILE => '증빙 서류',
            default => null
        };
    }
}
