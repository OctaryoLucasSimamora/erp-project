<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
// use App\Http\Controllers\ManufacturingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\BomController;
use App\Http\Controllers\ManufacturingOrderController;
use App\Http\Controllers\EmployeeController;


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

// Employee Management Routes - SEPARATE PAGES
Route::prefix('employee')->group(function () {
    // Department routes
    Route::get('/department', [EmployeeController::class, 'departmentIndex'])->name('employee.department.index');
    Route::get('/department/create', [EmployeeController::class, 'departmentCreate'])->name('employee.department.create');
    Route::post('/department', [EmployeeController::class, 'departmentStore'])->name('employee.department.store');
    Route::get('/department/{id}/edit', [EmployeeController::class, 'departmentEdit'])->name('employee.department.edit');
    Route::put('/department/{id}', [EmployeeController::class, 'departmentUpdate'])->name('employee.department.update');
    Route::delete('/department/{id}', [EmployeeController::class, 'departmentDestroy'])->name('employee.department.destroy');
    
    // Job Position routes
    Route::get('/job-position', [EmployeeController::class, 'jobPositionIndex'])->name('employee.job_position.index');
    Route::get('/job-position/create', [EmployeeController::class, 'jobPositionCreate'])->name('employee.job_position.create');
    Route::post('/job-position', [EmployeeController::class, 'jobPositionStore'])->name('employee.job_position.store');
    Route::get('/job-position/{id}/edit', [EmployeeController::class, 'jobPositionEdit'])->name('employee.job_position.edit');
    Route::put('/job-position/{id}', [EmployeeController::class, 'jobPositionUpdate'])->name('employee.job_position.update');
    Route::delete('/job-position/{id}', [EmployeeController::class, 'jobPositionDestroy'])->name('employee.job_position.destroy');
    
    // Employee routes
    Route::get('/employee', [EmployeeController::class, 'employeeIndex'])->name('employee.employee.index');
    Route::get('/employee/create', [EmployeeController::class, 'employeeCreate'])->name('employee.employee.create');
    Route::post('/employee', [EmployeeController::class, 'employeeStore'])->name('employee.employee.store');
    Route::get('/employee/{id}/edit', [EmployeeController::class, 'employeeEdit'])->name('employee.employee.edit');
    Route::put('/employee/{id}', [EmployeeController::class, 'employeeUpdate'])->name('employee.employee.update');
    Route::delete('/employee/{id}', [EmployeeController::class, 'employeeDestroy'])->name('employee.employee.destroy');
});

