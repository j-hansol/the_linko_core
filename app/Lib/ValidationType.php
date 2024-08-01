<?php

namespace App\Lib;

enum ValidationType : int {
    case MA     = 10;   // 필로폰
    case COC    = 20;   // 코카인
    case OPI    = 30;   // 아편
    case THC    = 40;   // 대마

    case CBC    = 100;  // 범죄사실 증명원
}
