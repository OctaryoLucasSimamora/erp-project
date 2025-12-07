<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
// use App\Http\Controllers\ManufacturingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\BomController;
use App\Http\Controllers\ManufacturingOrderController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Route::resource('manufacturing', ManufacturingController::class)->only(['index','create','store','show']);
Route::resource('products', ProductController::class);
Route::resource('raw-materials', RawMaterialController::class);
Route::resource('bom', BomController::class);
Route::resource('manufacturing', ManufacturingOrderController::class)->except(['show', 'destroy']);
Route::post('manufacturing/{id}/status', [ManufacturingOrderController::class, 'updateStatus'])
    ->name('manufacturing.status');


