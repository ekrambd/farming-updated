<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FarmersliderController;
use App\Http\Controllers\FarmercategoryController;
use App\Http\Controllers\FarmersubcategoryController;
use App\Http\Controllers\FarmerunitController;
use App\Http\Controllers\FarmeritemController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [IndexController::class, 'loginPage']);

Route::post('admin-login', [AccessController::class, 'adminLogin']);

Route::get('/logout', [AccessController::class, 'Logout']);


Route::group(['middleware' => 'prevent-back-history'],function(){
  
  //admin dashboard

    Route::get('/dashboard', [DashboardController::class, 'Dashboard']);

  //sliders
    Route::resource('farmersliders', FarmersliderController::class);

   //farmer categories
    Route::resource('farmercategories', FarmercategoryController::class);

   //farmer subcategories
    Route::resource('farmersubcategories', FarmersubcategoryController::class);

   //farmer units
    Route::resource('farmerunits', FarmerunitController::class);


});

Route::get('/users', function(){
	$users = \DB::table('users')->get();
	return $users;
});