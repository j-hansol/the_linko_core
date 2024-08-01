<?php
use App\Http\Controllers\Api\V1\ContractController;
use Illuminate\Support\Facades\Route;

Route::controller(ContractController::class)->group(function() {
    Route::get('/v1/contract/listUndisclosedContract', 'listUndisclosedContract')
        ->name('api.v1.contract.list_undisclosed_contract')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:order');
    Route::get('/v1/contract/listOrderedContract', 'listOrderedContract')
        ->name('api.v1.contract.list_ordered_contract')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:order,manager,manager_operator');
    Route::get('/v1/contract/listWorkerProvider', 'listWorkerProvider')
        ->name('api.v1.contract.list_worker_provider')
        ->middleware('auth:api', 'access_token', 'active_user:api');
    Route::post('/v1/contract/add', 'add')
        ->name('api.v1.contract.add')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:order');
    Route::post('/v1/contract/setSubContract/{id}', 'setSubContract')
        ->name('api.v1.contract.set_sub_contract')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:intermediary');
    Route::get('/v1/contract/get/{id}', 'setSubContract')
        ->name('api.v1.contract.set_get')
        ->middleware('auth:api', 'access_token', 'active_user:api');
    Route::post('/v1/contract/update/{id}', 'update')
        ->name('api.v1.contract.update')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:order');
    Route::get('/v1/contract/delete/{id}', 'delete')
        ->name('api.v1.contract.delete')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:order');
    Route::get('/v1/contract/listManager/{id}', 'listManager')
        ->name('api.v1.contract.list_manager')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:order,recipient');
    Route::post('/v1/contract/addManager/{contract_id}/{id}', 'addManager')
        ->name('api.v1.contract.add_manager')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:order,recipient');
    Route::post('/v1/contract/setManagers/{id}', 'addManager')
        ->name('api.v1.contract.set_managers')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:order,recipient');
    Route::get('/v1/contract/deleteManager/{contract_id}/{id}', 'deleteManager')
        ->name('api.v1.contract.delete_manager')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:order,recipient');
    Route::post('/v1/contract/addFile/{id}', 'addFile')
        ->name('api.v1.contract.add_file')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:order,recipient,manager,manager_operator,foreign_manager,foreign_manager_operator');
    Route::post('/v1/contract/updateFile/{id}', 'updateFile')
        ->name('api.v1.contract.update_file')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:order,recipient,manager,manager_operator,foreign_manager,foreign_manager_operator');
    Route::get('/v1/contract/deleteFile/{id}', 'deleteFile')
        ->name('api.v1.contract.delete_file')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:order,recipient,manager,manager_operator,foreign_manager,foreign_manager_operator');
    Route::get('/v1/contract/listWorker/{id}', 'listWorker')
        ->name('api.v1.contract.list_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api');
    Route::post('/v1/contract/assignWorkers/{id}', 'assignWorkers')
        ->name('api.v1.contract.assign_worker')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:foreign_manager,foreign_manager_operator');
    Route::get('/v1/contract/deleteWorker/{id}', 'deleteWorker')
        ->name('api.v1.contract.delete_worker')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:foreign_manager,foreign_manager_operator');
    Route::post('/v1/contract/updateWorkerStatus/{id}', 'updateWorkerStatus')
        ->name('api.v1.contract.update_worker_status')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:foreign_manager,foreign_manager_operator');
    Route::get('/v1/contract/listWorkingCompany/{id}', 'listWorkingCompany')
        ->name('api.v1.contract.list_working_company')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:order,manager,manager_operator');
    Route::post('/v1/contract/addWorkingCompanies/{id}', 'addWorkingCompanies')
        ->name('api.v1.contract.add_working_companies')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:manager,manager_operator');
    Route::post('/v1/contract/updatePlannedWorkerCount/{id}', 'updatePlannedWorkerCount')
        ->name('api.v1.contract.update_planned_worker_count')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:manager,manager_operator');
    Route::post('/v1/contract/deleteWorkingCompanies/{id}', 'deleteWorkingCompanies')
        ->name('api.v1.contract.delete_working_companies')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:manager,manager_operator');
    Route::post('/v1/contract/assignCompany/{id}', 'assignCompany')
        ->name('api.v1.contract.assign_company')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:manager,manager_operator');
    Route::get('/v1/contract/listEntrySchedule/{id}', 'listEntrySchedule')
        ->name('api.v1.contract.list_entry_schedule')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:manager,manager_operator');
    Route::post('/v1/contract/addEntrySchedule/{id}', 'addEntrySchedule')
        ->name('api.v1.contract.add_entry_schedule')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:manager,manager_operator');
    Route::post('/v1/contract/updateEntrySchedule/{id}', 'updateEntrySchedule')
        ->name('api.v1.contract.update_entry_schedule')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:manager,manager_operator');
    Route::get('/v1/contract/deleteEntrySchedule/{id}', 'deleteEntrySchedule')
        ->name('api.v1.contract.delete_entry_schedule')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:manager,manager_operator');
    Route::post('/v1/contract/setWorkerEntrySchedule/{id}', 'setWorkerEntrySchedule')
        ->name('api.v1.contract.set_worker_entry_schedule')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:foreign_manager,foreign_manager_operator');
    Route::post('/v1/contract/setWorkerEvaluationPlan/{id}', 'setWorkerEvaluationPlan')
        ->name('api.v1.contract.set_worker_evaluation_plan')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:manager,manager_operator');
    Route::post('/v1/contract/setCompanyEvaluationPlan/{id}', 'setCompanyEvaluationPlan')
        ->name('api.v1.contract.set_company_evaluation_plan')
        ->middleware(
            'auth:api', 'access_token', 'active_user:api',
            'member_type:manager,manager_operator');
});
