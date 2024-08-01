<?php
use Illuminate\Support\Facades\Route;

Route::controller(\App\Http\Controllers\Api\V1\EvaluationController::class)->group(function() {
    Route::get('/v1/evaluation/listEvaluation', 'listEvaluation')
        ->name('api.v1.evaluation.list_evaluation')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company,foreign_person');
    Route::post('/v1/evaluation/addEvaluation/{id}', 'addEvaluation')
        ->name('api.v1.evaluation.add_evaluation')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company,foreign_person');
    Route::get('/v1/evaluation/getEvaluation/{id}', 'getEvaluation')
        ->name('api.v1.evaluation.get_evaluation')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company,foreign_person');
    Route::post('/v1/evaluation/updateEvaluation/{id}', 'updateEvaluation')
        ->name('api.v1.evaluation.update_evaluation')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company,foreign_person');
    Route::get('/v1/evaluation/deleteEvaluation/{id}', 'deleteEvaluation')
        ->name('api.v1.evaluation.delete_evaluation')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:company,foreign_person');
});
