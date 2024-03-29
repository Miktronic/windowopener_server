<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [App\Http\Controllers\Api\Auth\AuthController::class,'authenticate']);
Route::post('/register', [App\Http\Controllers\Api\Auth\AuthController::class,'register']);

Route::prefix('v1')->group(function() {
    Route::prefix('user')->group(function(){
        Route::post('/login', [App\Http\Controllers\Api\Auth\AuthController::class,'authenticateAppUser']);
        Route::post('/register', [App\Http\Controllers\Api\Auth\AuthController::class,'registerAppUser']);
        Route::post('/forgotPassword', [App\Http\Controllers\Api\Auth\AuthController::class,'forgotPassword']);
        Route::post('/resetPassword', [App\Http\Controllers\Api\Auth\AuthController::class,'resetPassword']);
        Route::post('/emailVerification', [App\Http\Controllers\Api\Auth\AuthController::class,'emailVerification']);
        Route::post('/resendEmailVerificationCode', [App\Http\Controllers\Api\Auth\AuthController::class,'resendEmailVerificationCode']);
    });
    Route::prefix('geo')->group(function(){
        Route::get('/countries', [App\Http\Controllers\Api\GeoController::class,'getCountries']);
        Route::get('/states', [App\Http\Controllers\Api\GeoController::class,'getStates']);
        Route::get('/cities', [App\Http\Controllers\Api\GeoController::class,'getCities']);
        Route::get('/state/{id}/latlng', [App\Http\Controllers\Api\GeoController::class,'getLatLngByState']);
        Route::get('/city/{id}/latlng', [App\Http\Controllers\Api\GeoController::class,'getLatLngByCity']);
    });
});

Route::group(['middleware' => 'auth:sanctum'], function() {
    // APIs for APP USER
    Route::get('/auth/user', function (Request $request) {
        return ['data' => $request->user()];
    });

    //---- Admin APIs ----//
    Route::group(['middleware' => 'admin'], function() {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Api\DashboardController::class,'get']);

        // User management
        Route::get('/users', [App\Http\Controllers\Api\UserController::class,'get']);
        Route::post('/user', [App\Http\Controllers\Api\UserController::class,'create']);
        Route::delete('/user/{id}', [App\Http\Controllers\Api\UserController::class,'delete']);
        Route::get('/user-logs', [App\Http\Controllers\Api\UserLogController::class,'get']);

        // Device management
        Route::get('/devices', [App\Http\Controllers\Api\DeviceController::class,'get']);
        Route::post('/device', [App\Http\Controllers\Api\DeviceController::class,'store']);
        Route::delete('/device/{id}', [App\Http\Controllers\Api\DeviceController::class,'delete']);
        Route::post('/device-logs', [App\Http\Controllers\Api\DeviceLogController::class,'get']);
    });

    Route::delete('/logout', [App\Http\Controllers\Api\Auth\AuthController::class,'logout']);

    Route::prefix('v1')->middleware('verifyEmail')->group(function() {
        Route::prefix('user')->group(function(){
            Route::get('/profile', [App\Http\Controllers\Api\Auth\AuthController::class,'getProfile']);
            Route::post('/profile', [App\Http\Controllers\Api\Auth\AuthController::class,'updateProfile']);
            Route::post('/changePassword', [App\Http\Controllers\Api\Auth\AuthController::class,'changePasswordByAppUser']);
        });

        // Temperature
        Route::prefix('settings')->group(function(){
            Route::get('/', [App\Http\Controllers\Api\SettingsController::class, 'index']);
            Route::put('/', [App\Http\Controllers\Api\SettingsController::class, 'update']);
        });

        Route::prefix('device')->group(function(){
            Route::get('/', [App\Http\Controllers\Api\DeviceController::class,'getDevices']);
            Route::post('/', [App\Http\Controllers\Api\DeviceController::class,'store']);
            Route::get('/{id}', [App\Http\Controllers\Api\DeviceController::class,'getDeviceByApp']);
            Route::put('/{device}', [App\Http\Controllers\Api\DeviceController::class,'update']);
            Route::delete('/{id}', [App\Http\Controllers\Api\DeviceController::class,'delete']);
        });
        Route::prefix('log')->group(function(){
            Route::get('/', [App\Http\Controllers\Api\DeviceLogController::class,'index']);
            Route::post('/', [App\Http\Controllers\Api\DeviceLogController::class,'getLogsByApp']);
            Route::delete('/{id}', [App\Http\Controllers\Api\DeviceLogController::class,'deleteLogByApp']);
        });
    });

});
