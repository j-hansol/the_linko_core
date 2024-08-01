<?php

use App\Http\Controllers\Api\V1\CryptController;
use Illuminate\Support\Facades\Route;

Route::controller(CryptController::class)->group(function() {
    Route::post('/v1/crypt/encrypt', 'encrypt');
    Route::post('/v1/crypt/decrypt', 'decrypt');
    Route::post('/v1/crypt/encryptB64', 'encryptB64');
    Route::post('/v1/crypt/decryptB64', 'decryptB64');
});
