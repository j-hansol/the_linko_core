<?php

namespace App\Http\QueryParams;

use App\Models\Country;
use Illuminate\Foundation\Application;

class CountryParam {
    public ?Country $country;

    function __construct(Application $app) {
        $country_id = $app->make('request')->get('country_id');
        $this->country = Country::findMe($country_id);
    }
}
