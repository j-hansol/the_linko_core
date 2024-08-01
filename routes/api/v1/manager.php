<?php

use App\Http\Controllers\Api\V1\ManagerController;
use Illuminate\Support\Facades\Route;

Route::controller(ManagerController::class)->group(function() {
    Route::get('/v1/manager/listOperator', 'listOperator')
        ->name('api.v1.manager.list_operator')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:manager,foreign_manager');
    Route::post('/v1/manager/joinOperator', 'joinOperator')
        ->name('api.v1.manager.join_operator')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:manager,foreign_manager');
    Route::post('/v1/manager/setActiveOperator/{id}', 'setActiveOperator')
        ->name('api.v1.manager.set_active_operator')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:manager,foreign_manager');
    Route::get('/v1/manager/cancelOperator/{id}', 'cancelOperator')
        ->name('api.v1.manager.cancel_operator')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:manager,foreign_manager');
});
