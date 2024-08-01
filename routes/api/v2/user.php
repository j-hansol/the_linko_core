<?php

use App\Http\Controllers\Api\V2\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(UserController::class)->group(function() {
    Route::get('/v2/user/getManagerByCountry/{id}', 'getManagerByCountry')
        ->name('api.v2.user.get_manager_by_country');
    Route::post('/v2/user/login', 'login')
        ->name('api.v2.user.login');
    Route::post('/v2/user/loginByFacebook', 'loginByFacebook')
        ->name('api.v2.user.login_facebook');
    Route::post('/v2/user/loginAuto', 'loginAuto')
        ->name('api.v2.user.login_auto')
        ->middleware('auth:api');
    Route::get('/v2/user/logout', 'logout')
        ->name('api.v2.user.logout')
        ->middleware('auth:api', 'access_token');
    Route::post('/v2/user/updateDevice', 'updateDevice')
        ->name('api.v2.user.update_device')
        ->middleware('auth:api', 'access_token');
    Route::post('/v2/user/changePassword', 'changePassword')
        ->name('api.v2.user.change_password')
        ->middleware('auth:api', 'access_token:active');
    Route::post('/v2/user/updateFCMToken', 'updateFCMToken')
        ->name('api.v2.user.update_fcm_token')
        ->middleware('auth:api', 'access_token:active');
    Route::get('/v2/user/getIdByIdAlias/{id_alias}', 'getIdByIdAlias')
        ->name('api.v2.user.get_id_by_id_alias');
    Route::post('/v2/user/joinOrganization', 'joinOrganization')
        ->name('api.v2.user.join_organization');
    Route::post('/v2/user/joinPerson', 'joinPerson')
        ->name('api.v2.user.join_person');
    Route::post('/v2/user/updateOrganizationProfile', 'updateOrganizationProfile')
        ->name('api.v2.user.update_organization_profile')
        ->middleware('auth:api', 'access_token:active', 'active_user:api');
    Route::post('/v2/user/updatePersonProfile', 'updatePersonProfile')
        ->name('api.v2.user.update_person_profile')
        ->middleware('auth:api', 'access_token:active', 'active_user:api');
    Route::post('/v2/user/requestCertificationToken', 'requestCertificationToken')
        ->name('api.v2.user.request_certification_token');
    Route::post('/v2/user/resetPassword', 'resetPassword')
        ->name('api.v2.user.reset_password');
    Route::post('/v2/user/createPassword', 'createPassword')
        ->name('api.v2.user.create_password');
    Route::post('/v2/user/updatePhoto', 'updatePhoto')
        ->name('api.v2.user.update_photo')
        ->middleware('auth:api', 'access_token:active', 'active_user:api');
    Route::post('/v2/user/updateRoadMap', 'updateRoadMap')
        ->name('api.v2.user.update_road_map')
        ->middleware('auth:api', 'access_token:active', 'active_user:api');
    Route::get('/v2/user/getMyInfo', 'getMyInfo')
        ->name('api.v2.user.get_my_info')
        ->middleware('auth:api', 'access_token:active', 'active_user:api');
    Route::get('/v2/user/showPhoto/{id}', 'showPhoto')
        ->name('api.v2.user.show_photo')
        ->middleware('access_token_url');
    Route::get('/v2/user/showRoadMap/{id}', 'showRoadMap')
        ->name('api.v2.user.show_road_map')
        ->middleware('access_token_url');
});
