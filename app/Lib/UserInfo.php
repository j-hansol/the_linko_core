<?php

namespace App\Lib;

use App\Models\Country;

interface UserInfo {
    public function getName() : string;
    public function getCountry() : ?Country;
}
