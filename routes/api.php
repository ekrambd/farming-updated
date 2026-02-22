<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['throttle:60,1'])->group(function () {

	Route::post('categories', [ApiController::class, 'categories']);
	Route::get('/units', [ApiController::class, 'units']);

	Route::post('user-signup', [ApiController::class, 'userSignup']);
	Route::post('user-signin', [ApiController::class, 'userSignin']);

	Route::post('farmer-signup', [ApiController::class, 'farmerSignup']);
	Route::post('farmer-signin', [ApiController::class, 'farmerSignin']);
	
	

	Route::middleware('auth:sanctum')->group( function (){
		Route::post('user-signout', [ApiController::class, 'userSignOut']);
		Route::post('farmer-signout', [ApiController::class, 'farmerSignOut']);
		
		Route::get('/farmer-details/{id}', [ApiController::class, 'farmerDetails']);
		
		//sliders
		Route::get('/sliders', [ApiController::class, 'sliders']);

		//item upload
		Route::post('save-item', [ApiController::class, 'saveItem']);
		Route::post('/item-lists', [ApiController::class, 'itemLists']);
		Route::get('/item-details/{id}', [ApiController::class, 'itemDetails']);
		Route::post('/update-item/{id}', [ApiController::class, 'updateItem']);
		Route::get('/delete-item/{id}', [ApiController::class, 'deleteItem']); 
		Route::get('/single-image-delete/{id}', [ApiController::class, 'deleteItemImage']);

		//change password
		Route::post('change-password', [ApiController::class, 'changePassword']);

		//farmer profile update
		Route::post('farmer-profile-update', [ApiController::class, 'farmerProfileUpdate']);

		//orders
		Route::post('save-order', [ApiController::class, 'saveOrder']);
	});
});