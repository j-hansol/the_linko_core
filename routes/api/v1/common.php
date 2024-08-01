<?php

use App\Http\Controllers\Api\V1\CommonController;
use Illuminate\Support\Facades\Route;

Route::controller(CommonController::class)->group(function() {
    Route::get('/v1/common/listCountry', 'listCountry')
        ->name('api.v1.common.list_country');
    Route::get('/v1/common/getCountry/{id}', 'getCountry')
        ->name('api.v1.common.get_country');
    Route::get('/v1/common/listOccupationalGroup', 'listOccupationalGroup')
        ->name('api.v1.common.list_occupational_group');
    Route::get('/v1/common/getOccupationalGroup/{id}', 'getOccupationalGroup')
        ->name('api.v1.common.get_occupational_group');
    Route::get('/v1/common/listVisaDocumentType', 'listVisaDocumentType')
        ->name('api.v1.common.list_visa_document_type');
    Route::get('/v1/common/getVisaDocumentType/{id}', 'getVisaDocumentType')
        ->name('api.v1.common.get_visa_document_type');
    Route::get('/v1/common/listEvalInfo', 'listEvalInfo')
        ->name('api.v1.common.list_eval_info');
    Route::get('/v1/common/getEvaluation/{id}', 'getEvaluation')
        ->name('api.v1.common.get_evaluation');
});
