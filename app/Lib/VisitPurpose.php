<?php

namespace App\Lib;

use OpenApi\Attributes\Schema;

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
