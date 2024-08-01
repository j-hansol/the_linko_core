<?php

namespace App\Lib;

use App\Models\Country;

class DummyUser implements UserInfo {
    public string $name = 'No Name';
    public ?Country $country = null;
    public function __construct(string $name = 'No Name') {
        $this->name = $name;
        $this->country = Country::getDefault();
    }

    public function getName(): string {
        return $this->name;
    }

    public function getCountry(): ?Country {
        return $this->country;
    }
}
