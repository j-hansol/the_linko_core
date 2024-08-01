<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V2\WorkerController;
Route::controller(WorkerController::class)
    ->prefix('v2/worker')->name('api.v2.recommendation')->group(function () {
        Route::get('listWorkerForOperator', 'listWorkerForOperator')
            ->name('list_worker_for_operator')
            ->middleware(
                'auth:api', 'access_token:active', 'active_user:api',
                'member_type:operator'
            );
    });
