<?php

use App\Http\Controllers\Api\V1\CompanyController;
use Illuminate\Support\Facades\Route;

Route::controller(CompanyController::class)->group(function() {
    Route::get('/v1/company/listTask', 'listTask')
        ->name('api.v1.company.list_task')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company');
    Route::post('/v1/company/addTask', 'addTask')
        ->name('api.v1.company.add_task')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company');
    Route::get('/v1/company/getTask/{id}', 'getTask')
        ->name('api.v1.company.get_task')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company');
    Route::get('/v1/company/showTaskMovie/{id}', 'showTaskMovie')
        ->name('api.v1.company.show_task_movie')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'access_token_url', 'member_type:company');
    Route::post('/v1/company/updateTask/{id}', 'updateTask')
        ->name('api.v1.company.update_task')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company');
    Route::get('/v1/company/deleteTask/{id}', 'deleteTask')
        ->name('api.v1.company.delete_task')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company');
    Route::get('/v1/company/listActionPoint', 'listActionPoint')
        ->name('api.v1.company.list_action_point')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company');
    Route::post('/v1/company/addActionPoint', 'addActionPoint')
        ->name('api.v1.company.add_action_point')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company');
    Route::get('/v1/company/getActionPoint/{id}', 'getActionPoint')
        ->name('api.v1.company.get_action_point')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company');
    Route::post('/v1/company/updateActionPoint/{id}', 'updateActionPoint')
        ->name('api.v1.company.update_action_point')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company');
    Route::get('/v1/company/deleteActionPoint/{id}', 'deleteActionPoint')
        ->name('api.v1.company.delete_action_point')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company');
    Route::get('/v1/company/listWorker', 'listWorker')
        ->name('api.v1.company.list_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company');
    Route::get('/v1/company/getWorker/{id}', 'getWorker')
        ->name('api.v1.company.get_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company');
    Route::get('/v1/company/listWorkerActionPoint/{id}', 'listWorkerActionPoint')
        ->name('api.v1.company.list_worker_action_point')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company');
    Route::post('/v1/company/setWorkerActionPoint/{id}', 'setWorkerActionPoint')
        ->name('api.v1.company.set_worker_action_point')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company');
});
