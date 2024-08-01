<?php

namespace App\Lib;

enum BodyType : int {
    case HEAD           = 10;   // 머리
    case FACE           = 20;   // 얼굴
    case NECK           = 30;   // 목
    case NUCHA          = 40;   // 목덜미
    case SHOULDER       = 50;   // 어께
    case ARM            = 60;   // 팔
    case BRACHIUM       = 70;   // 위팔
    case ELBOW          = 80;   // 팔꿉
    case FOREARM        = 90;   // 야래팔
    case WRIST          = 100;  // 손목
    case HAND           = 110;  // 손
    case PALM_OF_HAND   = 120;  // 손바닥
    case DORSUM_OF_HAND = 130;  // 손등
    case FINGER         = 140;  // 손가락
    case CHEST          = 150;  // 가슴
    case BACK           = 160;  // 등
    case ABDOMEN        = 170;  // 복부
    case LUMBUS         = 180;  // 허리
    case HIP            = 190;  // 엉덩이
    case LOWER_LIMB     = 200;  // 하지
    case THIGH          = 210;  // 넙다리(상리 상부)
    case KNEE           = 220;  // 무릎
    case LEG            = 230;  // 종아리
    case FOOT           = 240;  // 발
    case DORSUM_OF_FOOT = 250;  // 발등
    case SOLE_OF_FOOT   = 260;  // 발바닥
    case TOE            = 270;  // 발가락
    case ALL            = 400;  // 전체
    case OTHER          = 990;  // rlxk
}
