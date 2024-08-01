<?php

use App\Http\Controllers\Api\V2\WorkerRecommendationController;
use Illuminate\Support\Facades\Route;

Route::controller(WorkerRecommendationController::class)
    ->prefix('v2/recommendation')
    ->name('api.v2.recommendation')->group(function() {
        Route::get('listRequest', 'listRequest')
            ->name('list_request')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator,partner,foreign_partner'
            );
        Route::get('listRequestForUser', 'listRequestForUser')
            ->name('list_request_for_user')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator,partner,foreign_partner'
            );
        Route::post('addRequest', 'addRequest')
            ->name('add_request')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator,partner,foreign_partner'
            );
        Route::get('getRequest/{id}', 'getRequest')
            ->name('get_request')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator,partner,foreign_partner'
            );
        Route::post('updateRequest/{id}', 'updateRequest')
            ->name('update_request')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator,partner,foreign_partner'
            );
        Route::get('deleteRequest/{id}', 'deleteRequest')
            ->name('delete_request')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator,partner,foreign_partner'
            );
        Route::post('setRequestStatus/{id}', 'setRequestStatus')
            ->name('set_request_status')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator'
            );
        Route::post('setRecommendation/{id}', 'setRecommendation')
            ->name('set_recommendation')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator'
            );
        Route::get('listRecommendation', 'listRecommendation')
            ->name('list_recommendation')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator,foreign_partner'
            );
        Route::get('listRecommendationForUser', 'listRecommendationForUser')
            ->name('list_recommendation_for_user')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator,partner,foreign_partner'
            );
        Route::post('addRecommendation', 'addRecommendation')
            ->name('add_recommendation')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator'
            );
        Route::get('deleteRecommendation/{id}', 'deleteRecommendation')
            ->name('delete_recommendation')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator'
            );
        Route::get('setRecommendationStatus/{id}', 'setRecommendationStatus')
            ->name('set_recommendation_status')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator'
            );
        Route::get('getRecommendation/{id}', 'getRecommendation')
            ->name('get_recommendation')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator,partner,foreign_partner'
            );
        Route::get('getRecommendationByRequest/{id}', 'getRecommendationByRequest')
            ->name('get_recommendation_by_request')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator,partner,foreign_partner'
            );
        Route::get('listRecommendedWorker/{id}', 'listRecommendedWorker')
            ->name('list_recommendation_worker')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator,partner,foreign_partner'
            );
        Route::get('getWorkerInfo/{id}', 'getWorkerInfo')
            ->name('get_worker_info')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator,partner,foreign_partner'
            );
        Route::post('addRecommendedWorkers/{id}', 'addRecommendedWorkers')
            ->name('add_recommended_workers')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator'
            );
        Route::post('deleteRecommendedWorkers/{id}', 'deleteRecommendedWorkers')
            ->name('delete_recommended_workers')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator'
            );
        Route::post('setRecommendedWorkerStatus/{id}', 'setRecommendedWorkerStatus')
            ->name('set_recommended_worker_status')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator'
            );
});
