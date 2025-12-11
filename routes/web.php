<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
// use App\Http\Controllers\ManufacturingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\BomController;
use App\Http\Controllers\ManufacturingOrderController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\RFQController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\VendorBillController;


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
// Purchase Module Routes
Route::prefix('purchase')->group(function () {
    
    // ==================== VENDOR ROUTES ====================
    Route::get('/vendor', [VendorController::class, 'index'])->name('purchase.vendor.index');
    Route::get('/vendor/create', [VendorController::class, 'create'])->name('purchase.vendor.create');
    Route::post('/vendor', [VendorController::class, 'store'])->name('purchase.vendor.store');
    Route::get('/vendor/{id}/edit', [VendorController::class, 'edit'])->name('purchase.vendor.edit');
    Route::put('/vendor/{id}', [VendorController::class, 'update'])->name('purchase.vendor.update');
    Route::delete('/vendor/{id}', [VendorController::class, 'destroy'])->name('purchase.vendor.destroy');
    
    // ==================== RFQ ROUTES ====================
    Route::get('/rfq', [RFQController::class, 'index'])->name('purchase.rfq.index');
    Route::get('/rfq/create', [RFQController::class, 'create'])->name('purchase.rfq.create');
    Route::post('/rfq', [RFQController::class, 'store'])->name('purchase.rfq.store');
    Route::get('/rfq/{id}/edit', [RFQController::class, 'edit'])->name('purchase.rfq.edit');
    Route::put('/rfq/{id}', [RFQController::class, 'update'])->name('purchase.rfq.update');
    Route::delete('/rfq/{id}', [RFQController::class, 'destroy'])->name('purchase.rfq.destroy');
    
    // RFQ Additional Routes
    Route::post('/rfq/{id}/status', [RFQController::class, 'updateStatus'])->name('purchase.rfq.status');
    Route::get('/rfq/{id}/convert-to-po', [RFQController::class, 'convertToPO'])->name('purchase.rfq.convert-to-po');
    
    // ==================== PURCHASE ORDER ROUTES ====================
    Route::get('/po', [PurchaseOrderController::class, 'index'])->name('purchase.po.index');
    Route::get('/po/create', [PurchaseOrderController::class, 'create'])->name('purchase.po.create');
    Route::post('/po', [PurchaseOrderController::class, 'store'])->name('purchase.po.store');
    Route::get('/po/{id}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase.po.edit');
    Route::put('/po/{id}', [PurchaseOrderController::class, 'update'])->name('purchase.po.update');
    Route::delete('/po/{id}', [PurchaseOrderController::class, 'destroy'])->name('purchase.po.destroy');
    
    // PO Additional Routes
    Route::post('/po/{id}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase.po.status');
    Route::get('/po/{id}/convert-to-bill', [PurchaseOrderController::class, 'convertToVendorBill'])->name('purchase.po.convert-to-bill');
    
    // ==================== VENDOR BILL ROUTES ====================
    Route::get('/vendor-bill', [VendorBillController::class, 'index'])->name('purchase.vendor-bill.index');
    Route::get('/vendor-bill/create', [VendorBillController::class, 'create'])->name('purchase.vendor-bill.create');
    Route::post('/vendor-bill', [VendorBillController::class, 'store'])->name('purchase.vendor-bill.store');
    Route::get('/vendor-bill/{id}/edit', [VendorBillController::class, 'edit'])->name('purchase.vendor-bill.edit');
    Route::put('/vendor-bill/{id}', [VendorBillController::class, 'update'])->name('purchase.vendor-bill.update');
    Route::delete('/vendor-bill/{id}', [VendorBillController::class, 'destroy'])->name('purchase.vendor-bill.destroy');
    
    // Vendor Bill Additional Routes
    Route::post('/vendor-bill/{id}/update-status', [VendorBillController::class, 'updateStatus'])->name('purchase.vendor-bill.update-status');
    Route::get('/vendor-bill/{id}/make-payment', [VendorBillController::class, 'createPayment'])->name('purchase.vendor-bill.make-payment');
    Route::post('/vendor-bill/{id}/process-payment', [VendorBillController::class, 'processPayment'])->name('purchase.vendor-bill.process-payment');
    Route::get('/vendor-bill/create-from-po/{poId}', [VendorBillController::class, 'convertFromPO'])->name('purchase.vendor-bill.create-from-po');
});