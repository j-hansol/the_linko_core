<?php

use App\Http\Controllers\Api\V1\WorkerController;
use Illuminate\Support\Facades\Route;


Route::controller(WorkerController::class)->group(function() {
    Route::get('/v1/worker/listWorker', 'listWorker')
        ->name('api.v1.worker.list_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/joinWorker', 'joinWorker')
        ->name('api.v1.worker.join_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/getWorker/{id}', 'getWorker')
        ->name('api.v1.worker.get_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/updateWorker/{id}', 'updateWorker')
        ->name('api.v1.worker.update_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/updateWorkerPhoto/{id}', 'updateWorkerPhoto')
        ->name('api.v1.worker.update_worker_photo')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/getInitialPassword/{id}', 'getInitialPassword')
        ->name('api.v1.worker.get_initial_password')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/cancelManaging/{id}', 'cancelManaging')
        ->name('api.v1.worker.cancel_managing')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/listPreSavedWorker', 'listPreSavedWorker')
        ->name('api.v1.worker.list_pre_save_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/preSaveWorker', 'preSaveWorker')
        ->name('api.v1.worker.pre_save_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/preSaveWorkerFromExcel', 'preSaveWorkerFromExcel')
        ->name('api.v1.worker.pre_save_worker_from_excel')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/updatePreSavedWorker/{id}', 'updatePreSavedWorker')
        ->name('api.v1.worker.update_pre_saved_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/deletePreSavedWorker/{id}', 'deletePreSavedWorker')
        ->name('api.v1.worker.delete_pre_saved_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');

    Route::get('/v1/worker/getInfo/{id}', 'getInfo')
        ->name('api.v1.worker.get_info')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/getInfoOrNull/{id}', 'getInfoOrNull')
        ->name('api.v1.worker.get_info_or_null')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/setInfo/{id}', 'setInfo')
        ->name('api.v1.worker.set_info')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/listVisitedCountry/{id}', 'listVisitedCountry')
        ->name('api.v1.worker.list_visited_country')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/getVisitedCountry/{id}', 'getVisitedCountry')
        ->name('api.v1.worker.get_visited_country')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/addVisitedCountry/{id}', 'addVisitedCountry')
        ->name('api.v1.worker.add_visited_country')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/updateVisitedCountry/{id}', 'updateVisitedCountry')
        ->name('api.v1.worker.update_visited_country')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/deleteVisitedCountry/{id}', 'deleteVisitedCountry')
        ->name('api.v1.worker.delete_visited_country/{id}')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/listFamily/{id}', 'listFamily')
        ->name('api.v1.worker.list_family')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/getFamily/{id}', 'getFamily')
        ->name('api.v1.worker.get_family')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/addFamily/{id}', 'addFamily')
        ->name('api.v1.worker.add_family')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/updateFamily/{id}', 'updateFamily')
        ->name('api.v1.worker.update_family')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/deleteFamily/{id}', 'deleteFamily')
        ->name('api.v1.worker.delete_family')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/addPassportForWorker/{id}', 'addPassportForWorker')
        ->name('api.v1.worker.add_passport_for_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/addPassportForWorkerFromForm/{id}', 'addPassportForWorkerFromForm')
        ->name('api.v1.worker.add_passport_for_worker_from_form')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/updatePassportForWorkerFromForm/{id}', 'updatePassportForWorkerFromForm')
        ->name('api.v1.worker.update_passport_for_worker_from_form')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/deletePassport/{id}', 'deletePassport')
        ->name('api.v1.worker.delete_passport')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/setPassportFile/{id}', 'setPassportFile')
        ->name('api.v1.worker.set_passport_file')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/listPassportForWorker/{id}', 'listPassportForWorker')
        ->name('api.v1.worker.list_passport_for_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/getPassport/{id}', 'getPassport')
        ->name('api.v1.worker.get_passport')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/showPassportFile/{id}', 'showPassportFile')
        ->name('api.v1.worker.show_passport_file')
        ->middleware( 'access_token_url', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/listVisaDocument/{id}', 'listVisaDocument')
        ->name('api.v1.worker.list_visa_document')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/addVisaDocumentForWorker/{id}', 'addVisaDocumentForWorker')
        ->name('api.v1.worker.add_visa_document_for_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/addVisaDocumentsForWorker/{id}', 'addVisaDocumentsForWorker')
        ->name('api.v1.worker.add_visa_documents_for_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/updateVisaDocument/{id}', 'updateVisaDocument')
        ->name('api.v1.worker.update_visa_document')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/showVisaDocument/{id}', 'showVisaDocument')
        ->name('api.v1.worker.show_visa_document')
        ->middleware('access_token_url');
    Route::get('/v1/worker/deleteVisaDocument/{id}', 'deleteVisaDocument')
        ->name('api.v1.worker.delete_visa_document')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/listResume', 'listResume')
        ->name('api.v1.worker.list_resume')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person');
    Route::get('/v1/worker/listWorkerResume/{id}', 'listWorkerResume')
        ->name('api.v1.worker.list_worker_resume')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/addResume', 'addResume')
        ->name('api.v1.worker.list_resume')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person');
    Route::post('/v1/worker/addWorkerResume/{id}', 'addWorkerResume')
        ->name('api.v1.worker.list_worker_resume')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/updateResume/{id}', 'updateResume')
        ->name('api.v1.worker.update_resume')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/deleteResume/{id}', 'deleteResume')
        ->name('api.v1.worker.delete_resume')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/showResume/{id}', 'showResume')
        ->name('api.v1.worker.show_resume_file')
        ->middleware('access_token_url', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/listExperience', 'listExperience')
        ->name('api.v1.worker.list_experience')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person');
    Route::get('/v1/worker/listWorkerExperience/{id}', 'listWorkerExperience')
        ->name('api.v1.worker.list_worker_experience')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/addExperience', 'addExperience')
        ->name('api.v1.worker.list_experience')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person');
    Route::post('/v1/worker/addWorkerExperience/{id}', 'addWorkerExperience')
        ->name('api.v1.worker.list_worker_experience')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/updateExperience/{id}', 'updateExperience')
        ->name('api.v1.worker.update_experience')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/deleteExperience/{id}', 'deleteExperience')
        ->name('api.v1.worker.delete_experience')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/getExperience/{id}', 'getExperience')
        ->name('api.v1.worker.get_experience')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/showExperienceFile/{id}', 'showExperienceFile')
        ->name('api.v1.worker.show_experience_file')
        ->middleware('access_token_url');
    Route::get('/v1/worker/listWorkerEtcExperienceFile/{id}', 'listWorkerEtcExperienceFile')
        ->name('api.v1.worker.list_worker_etc_experience_file')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/addEtcExperienceFile', 'addEtcExperienceFile')
        ->name('api.v1.worker.list_etc_experience_file')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person');
    Route::post('/v1/worker/addWorkerEtcExperienceFile/{id}', 'addWorkerEtcExperienceFile')
        ->name('api.v1.worker.list_worker_etc_experience_file')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/updateEtcExperienceFile/{id}', 'updateEtcExperienceFile')
        ->name('api.v1.worker.update_experience_file')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/deleteEtcExperienceFile/{id}', 'deleteEtcExperienceFile')
        ->name('api.v1.worker.delete_etc_experience_file')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/showEtcExperienceFile/{id}', 'showEtcExperienceFile')
        ->name('api.v1.worker.show_etc_experience_file')
        ->middleware('access_token_url', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');

    Route::get('/v1/worker/listWorkerEducation/{id}', 'listWorkerEducation')
        ->name('api.v1.worker.list_worker_education')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/getWorkerEducation/{id', 'getWorkerEducation')
        ->name('api.v1.worker.get_worker_education')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/addWorkerEducation/{id}', 'addWorkerEducation')
        ->name('api.v1.worker.add_worker_education')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/worker/updateWorkerEducation/{id}', 'updateWorkerEducation')
        ->name('api.v1.worker.update_worker_education')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/deleteWorkerEducation/{id}', 'deleteWorkerEducation')
        ->name('api.v1.worker.delete_worker_education')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/worker/showWorkerEducationFile/{id}', 'showWorkerEducationFile')
        ->name('api.v1.worker.show_worker_education_file')
        ->middleware('access_token_url', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
});
