<?php

use App\Http\Controllers\Api\V2\CommonController;
use Illuminate\Support\Facades\Route;

Route::controller(CommonController::class)->group(function() {
    Route::get('/v2/common/listCountry', 'listCountry')
        ->name('api.v2.common.list_country');
    Route::get('/v2/common/getCountry/{id}', 'getCountry')
        ->name('api.v2.common.get_country');
    Route::get('/v2/common/listOccupationalGroup', 'listOccupationalGroup')
        ->name('api.v2.common.list_occupational_group');
    Route::get('/v2/common/getOccupationalGroup/{id}', 'getOccupationalGroup')
        ->name('api.v2.common.get_occupational_group');
    Route::get('/v2/common/listVisaDocumentType', 'listVisaDocumentType')
        ->name('api.v2.common.list_visa_document_type');
    Route::get('/v2/common/getVisaDocumentType/{id}', 'getVisaDocumentType')
        ->name('api.v2.common.get_visa_document_type');
    Route::get('/v2/common/listEvalInfo', 'listEvalInfo')
        ->name('api.v2.common.list_eval_info');
    Route::get('/v2/common/getEvaluation/{id}', 'getEvaluation')
        ->name('api.v2.common.get_evaluation');
});
