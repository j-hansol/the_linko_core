<?php

use App\Http\Controllers\Api\V1\ManagerPoolController;
use Illuminate\Support\Facades\Route;

Route::controller(ManagerPoolController::class)->group(function() {
    Route::get('/v1/manager_pool/listManager', 'listManager')
        ->name('api.v1.manager_pool.list_manager')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:order,recipient');
    Route::get('/v1/manager_pool/listAbleManager', 'listAbleManager')
        ->name('api.v1.manager_pool.list_able_manager')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:order,recipient');
    Route::get('/v1/manager_pool/add', 'add')
        ->name('api.v1.manager_pool.add')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:order,recipient');
    Route::get('/v1/manager_pool/delete', 'delete')
        ->name('api.v1.manager_pool.delete')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:order,recipient');
});
