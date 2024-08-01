<?php

use App\Http\Controllers\Api\V1\UtilController;
use Illuminate\Support\Facades\Route;

Route::controller(UtilController::class)->group(function() {
    Route::get('/v1/util/genUUID', 'genUUID')
        ->name('api.v1.util.gen_uuid');
    Route::get('/v1/util/getCountry/{country}', 'getCountry')
        ->name('api.v1.util.get_country');
});
