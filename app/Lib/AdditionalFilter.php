<?php

namespace App\Lib;

class AdditionalFilter {
    function __construct(public string $field, public string $op, public string $value) {}
}
