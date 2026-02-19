<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AjaxController;

Route::post('category-status-update', [AjaxController::class, 'categoryStatusUpdate']);
Route::post('subcategory-status-update', [AjaxController::class, 'subcategoryStatusUpdate']);
Route::post('unit-status-update', [AjaxController::class, 'unitStatusUpdate']);