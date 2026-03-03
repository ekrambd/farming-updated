<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AjaxController;

Route::post('category-status-update', [AjaxController::class, 'categoryStatusUpdate']);
Route::post('subcategory-status-update', [AjaxController::class, 'subcategoryStatusUpdate']);
Route::post('unit-status-update', [AjaxController::class, 'unitStatusUpdate']);
Route::post('/farmer-status-update', [AjaxController::class, 'farmerStatusUpdate']);
Route::get('/delete-farmer', [AjaxContrller::class, 'deleteFarmer']);