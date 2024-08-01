<?php

use App\Http\Controllers\Api\V1\OperatorController;
use Illuminate\Support\Facades\Route;

Route::controller(OperatorController::class)->group(function() {
    Route::get('/v1/operator/switchUser/{id}', 'switchUser')
        ->name('api.v1.operator.switch_user')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::get('/v1/operator/exitSwitchedUser', 'exitSwitchedUser')
        ->name('api.v1.operator.exit_switched_user')
        ->middleware('auth:api', 'access_token', 'active_user:api');
    Route::post('/v1/operator/updateOccupationalGroup/{id}', 'updateOccupationalGroup')
        ->name('api.v1.operator.update_occupational_group')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::get('/v1/operator/listUser', 'listUser')
        ->name('api.v1.operator.list_user')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::post('/v1/operator/updateUserActiveState/{id}', 'updateUserActiveState')
        ->name('api.v1.operator.update_user_active_state')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::post('/v1/operator/updateUserType/{id}', 'updateUserType')
        ->name('api.v1.operator.update_user_type')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::post('/v1/operator/addVisaDocumentType', 'addVisaDocumentType')
        ->name('api.v1.operator.add_visa_document_type')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::post('/v1/operator/updateVisaDocumentType/{id}', 'updateVisaDocumentType')
        ->name('api.v1.operator.update_visa_document_type')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::post('/v1/operator/deleteVisaDocumentType/{id}', 'deleteVisaDocumentType')
        ->name('api.v1.operator.delete_visa_document_type')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::post('/v1/operator/addEvalInfo', 'addEvalInfo')
        ->name('api.v1.operator.add_eval_info')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::post('/v1/operator/updateEvalInfo/{id}', 'updateEvalInfo')
        ->name('api.v1.operator.update_eval_info')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::get('/v1/operator/deleteEvalInfo/{id}', 'deleteEvalInfo')
        ->name('api.v1.operator.delete_eval_info')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::post('/v1/operator/addEvalItem/{id}', 'addEvalItem')
        ->name('api.v1.operator.add_eval_item')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::post('/v1/operator/updateEvalItem/{id}', 'updateEvalItem')
        ->name('api.v1.operator.update_eval_item')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::get('/v1/operator/deleteEvalItem/{id}', 'deleteEvalItem')
        ->name('api.v1.operator.delete_eval_item')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
});
