<?php

namespace App\Lib;

enum MemberTypeGroup {
    case ORGANIZATION;
    case OPERATOR;
    case MANAGER_OPERATOR;
    case PERSON;
    case FOREIGN_ORGANIZATION;
    case FOREIGN_MANAGER_OPERATOR;
    case FOREIGN_PERSON;
}
