<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
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

Route::group(['middleware'=>'api'], function($routes){
    Route::get('/usuarios',[UserController::class,'index']);
    Route::post('/register',[UserController::class,'register']);
    Route::post('/login',[UserController::class,'login']);
    Route::get('/logout',[UserController::class,'logout']);
    Route::get('/profile',[UserController::class,'profile']);
    Route::get('/profile-update',[UserController::class, 'updateProfile']);
    Route::get('/send-verify-mail/{email}',[UserController::class,'sendVerifyMail']);
});

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});
