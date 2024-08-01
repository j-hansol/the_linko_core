<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\VisaController;

Route::controller(VisaController::class)->group(function() {
    Route::get('/v1/visa/listVisa', 'listVisa')
        ->name('api.visa.list_visa')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person');
    Route::get('/v1/visa/listWorkerVisa/{id}', 'listWorkerVisa')
        ->name('api.visa.list_worker_visa')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::get('/v1/visa/listConsultAbleVisa', 'listConsultAbleVisa')
        ->name('api.visa.list_consult_able_visa')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator,attorney');
    Route::get('/v1/visa/listConsultingVisa', 'listConsultingVisa')
        ->name('api.visa.list_consulting_visa')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:attorney');
    Route::get('/v1/visa/listConsultedVisa', 'listConsultedVisa')
        ->name('api.visa.list_consulted_visa')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:attorney');
    Route::post('/v1/visa/request', 'request')
        ->name('api.visa.request')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person');
    Route::post('/v1/visa/requestForWorker/{id}', 'requestForWorker')
        ->name('api.visa.request_for_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::get('/v1/visa/get/{id}', 'get')
        ->name('api.visa.get')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator,foreign_person,foreign_manager,foreign_manager_operator,attorney');
    Route::post('/v1/visa/update/{id}', 'update')
        ->name('api.visa.update')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/updateStatus/{id}', 'updateStatus')
        ->name('api.visa.update_status')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator,attorney');
    Route::get('/v1/visa/getAvailableStatus/{id}', 'getAvailableStatus')
        ->name('api.visa.get_available_status')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator,attorney');
    Route::get('/v1/visa/delete/{id}', 'delete')
        ->name('api.visa.delete')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/updateProfile/{id}', 'updateProfile')
        ->name('api.visa.update_profile')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/setPhoto/{id}', 'setPhoto')
        ->name('api.visa.set_photo')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/visa/showPhoto/{id}', 'showPhoto')
        ->name('api.visa.show_photo')
        ->middleware('access_token_url');
    Route::post('/v1/visa/setPassport/{id}', 'setPassport')
        ->name('api.visa.set_passport')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/visa/showPassportFile/{id}', 'showPassportFile')
        ->name('api.visa.show_passport_file')
        ->middleware('access_token_url');
    Route::post('/v1/visa/setContact/{id}', 'setContact')
        ->name('api.visa.set_contact')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/setFamilyDetail/{id}', 'setFamilyDetail')
        ->name('api.visa.set_family_detail')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/setEducation/{id}', 'setEducation')
        ->name('api.visa.set_education')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/setEmployment/{id}', 'setEmployment')
        ->name('api.visa.set_employment')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/setVisitDetail/{id}', 'setVisitDetail')
        ->name('api.visa.set_visit_detail')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/setVisitKoreaHistory/{id}', 'setVisitKoreaHistory')
        ->name('api.visa.set_visit_korea_history')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/setVisitOtherCountryHistory/{id}', 'setVisitOtherCountryHistory')
        ->name('api.visa.set_visit_other_country_history')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/setStayFamilyInKorea/{id}', 'setStayFamilyInKorea')
        ->name('api.visa.set_stay_family_in_korea')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/setFamilyMember/{id}', 'setFamilyMember')
        ->name('api.visa.set_family_member')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/setInvitor/{id}', 'setInvitor')
        ->name('api.visa.set_visitor')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/setFundingDetail/{id}', 'setFundingDetail')
        ->name('api.visa.set_funding_detail')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/setAssistance/{id}', 'setAssistance')
        ->name('api.visa.set_assistance')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/addVisaDocument/{id}', 'addVisaDocument')
        ->name('api.visa.add_visa_document')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/updateVisaDocument/{id}', 'updateVisaDocument')
        ->name('api.visa.update_visa_document')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::get('/v1/visa/showVisaDocument/{id}', 'showVisaDocument')
        ->name('api.v1.visa.show_visa_document')
        ->middleware('access_token_url');
    Route::get('/v1/visa/deleteVisaDocument/{id}', 'deleteVisaDocument')
        ->name('api.v1.visa.delete_visa_document')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/sendMessage/{id}', 'sendMessage')
        ->name('api.v1.visa.send_message')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator,attorney');
    Route::get('/v1/visa/requestConsultingPermission/{id}', 'requestConsultingPermission')
        ->name('api.v1.operator.visa.request_consulting_permission')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:attorney');
    Route::get('/v1/visa/listRequestPermission/{id}', 'listRequestPermission')
        ->name('api.v1.operator.visa.list_request__permission')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::get('/v1/visa/setConsultingAttorney/{attorney_id}/{id}', 'setConsultingAttorney')
        ->name('api.v1.operator.visa.set_consulting_attorney')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::post('/v1/visa/setConsultingAttorneyMultiple/{attorney_id}', 'setConsultingAttorneyMultiple')
        ->name('api.v1.operator.visa.set_consulting_attorney_multiple')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::get('/v1/visa/listConsultingVisaForOperator', 'listConsultingVisaForOperator')
        ->name('api.v1.operator.visa.get_visa')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::get('/v1/visa/listConsultedVisaForOperator', 'listConsultedVisaForOperator')
        ->name('api.v1.operator.visa.list_consulted_visa')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::post('/v1/visa/assignIssueTaskMultiple/{attorney_id}', 'assignIssueTaskMultiple')
        ->name('api.v1.operator.visa.assign_issue_task_multiple')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:operator');
    Route::post('/v1/visa/requestFromJsonForWorker/{id}', 'requestFromJsonForWorker')
        ->name('api.v1.operator.visa.request_from_json_for_worker')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_manager,foreign_manager_operator');
    Route::post('/v1/visa/updatePassportFromJson/{id}', 'updatePassportFromJson')
        ->name('api.v1.operator.visa.update_passport_from_json')
        ->middleware('auth:api', 'access_token', 'active_user:api', 'member_type:foreign_person,foreign_manager,foreign_manager_operator');
});
