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
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\DeliveryOrderController;
use App\Http\Controllers\CustomerInvoiceController;


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

    // ========== VENDOR ROUTES ==========
    Route::get('/vendor', [VendorController::class, 'index'])->name('purchase.vendor.index');
    Route::get('/vendor/create', [VendorController::class, 'create'])->name('purchase.vendor.create');
    Route::post('/vendor', [VendorController::class, 'store'])->name('purchase.vendor.store');
    Route::get('/vendor/{id}/edit', [VendorController::class, 'edit'])->name('purchase.vendor.edit');
    Route::put('/vendor/{id}', [VendorController::class, 'update'])->name('purchase.vendor.update');
    Route::delete('/vendor/{id}', [VendorController::class, 'destroy'])->name('purchase.vendor.destroy');

    // ========== RFQ ROUTES ==========
    Route::get('/rfq', [RFQController::class, 'index'])->name('purchase.rfq.index');
    Route::get('/rfq/create', [RFQController::class, 'create'])->name('purchase.rfq.create');
    Route::post('/rfq', [RFQController::class, 'store'])->name('purchase.rfq.store');
    Route::get('/rfq/{id}/edit', [RFQController::class, 'edit'])->name('purchase.rfq.edit');
    Route::put('/rfq/{id}', [RFQController::class, 'update'])->name('purchase.rfq.update');
    Route::delete('/rfq/{id}', [RFQController::class, 'destroy'])->name('purchase.rfq.destroy');
    Route::post('/rfq/{id}/status', [RFQController::class, 'updateStatus'])->name('purchase.rfq.status');
    Route::get('/rfq/{id}/convert', [RFQController::class, 'convertToPO'])->name('purchase.rfq.convert');

    // ========== PURCHASE ORDER ROUTES ==========
    Route::get('/po', [PurchaseOrderController::class, 'index'])->name('purchase.po.index');
    Route::get('/po/create', [PurchaseOrderController::class, 'create'])->name('purchase.po.create');
    Route::post('/po', [PurchaseOrderController::class, 'store'])->name('purchase.po.store');
    Route::get('/po/{id}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase.po.edit');
    Route::put('/po/{id}', [PurchaseOrderController::class, 'update'])->name('purchase.po.update');
    Route::delete('/po/{id}', [PurchaseOrderController::class, 'destroy'])->name('purchase.po.destroy');
    Route::post('/po/{id}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase.po.status');
    Route::get('/po/{id}/convert', [PurchaseOrderController::class, 'convertToVendorBill'])->name('purchase.po.convert');

    // ========== VENDOR BILL ROUTES ==========
    Route::get('/vendor-bill', [VendorBillController::class, 'index'])->name('purchase.vendor-bill.index');
    Route::get('/vendor-bill/create', [VendorBillController::class, 'create'])->name('purchase.vendor-bill.create');
    Route::post('/vendor-bill', [VendorBillController::class, 'store'])->name('purchase.vendor-bill.store');
    Route::get('/vendor-bill/{id}/edit', [VendorBillController::class, 'edit'])->name('purchase.vendor-bill.edit');
    Route::put('/vendor-bill/{id}', [VendorBillController::class, 'update'])->name('purchase.vendor-bill.update');
    Route::delete('/vendor-bill/{id}', [VendorBillController::class, 'destroy'])->name('purchase.vendor-bill.destroy');
    Route::post('/vendor-bill/{id}/status', [VendorBillController::class, 'updateStatus'])->name('purchase.vendor-bill.status');
    Route::get('/vendor-bill/{id}/payment', [VendorBillController::class, 'createPayment'])->name('purchase.vendor-bill.payment.create');
    Route::post('/vendor-bill/{id}/payment', [VendorBillController::class, 'processPayment'])->name('purchase.vendor-bill.payment.process');
    Route::get('/vendor-bill/convert/{poId}', [VendorBillController::class, 'convertFromPO'])->name('purchase.vendor-bill.convert');
});
// Sales - Quotation Routes
Route::prefix('sales')->name('sales.')->group(function () {
    Route::get('/quotation', [QuotationController::class, 'index'])->name('quotation.index');
    Route::get('/quotation/create', [QuotationController::class, 'create'])->name('quotation.create');
    Route::post('/quotation', [QuotationController::class, 'store'])->name('quotation.store');
    Route::get('/quotation/{id}/edit', [QuotationController::class, 'edit'])->name('quotation.edit');
    Route::put('/quotation/{id}', [QuotationController::class, 'update'])->name('quotation.update');
    Route::delete('/quotation/{id}', [QuotationController::class, 'destroy'])->name('quotation.destroy');
    Route::post('/quotation/{id}/status', [QuotationController::class, 'updateStatus'])->name('quotation.status');
    Route::get('/quotation/{id}/convert', [QuotationController::class, 'convertToSalesOrder'])->name('quotation.convert');
});

// Sales Order Routes
Route::prefix('sales/order')->name('sales.order.')->group(function () {
    Route::get('/', [SalesOrderController::class, 'index'])->name('index');
    Route::get('/create', [SalesOrderController::class, 'create'])->name('create');
    Route::post('/', [SalesOrderController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [SalesOrderController::class, 'edit'])->name('edit');
    Route::put('/{id}', [SalesOrderController::class, 'update'])->name('update');
    Route::delete('/{id}', [SalesOrderController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/confirm', [SalesOrderController::class, 'confirmOrder'])->name('confirm');
    Route::post('/{id}/lock', [SalesOrderController::class, 'lockOrder'])->name('lock');
});

// Sales Order Routes
Route::prefix('sales/delivery')->name('sales.delivery.')->group(function () {
    Route::get('/', [DeliveryOrderController::class, 'index'])->name('index');
    Route::get('/create', [DeliveryOrderController::class, 'create'])->name('create');
    Route::post('/', [DeliveryOrderController::class, 'store'])->name('store');
    Route::get('/{id}', [DeliveryOrderController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [DeliveryOrderController::class, 'edit'])->name('edit');
    Route::put('/{id}', [DeliveryOrderController::class, 'update'])->name('update');
    Route::delete('/{id}', [DeliveryOrderController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/status', [DeliveryOrderController::class, 'updateStatus'])->name('update.status');
    Route::get('/sales-order/{salesOrderId}/items', [DeliveryOrderController::class, 'getSalesOrderItems'])
        ->name('sales.order.items');
});

// Sales Invoice Routes
// Customer Invoice Routes
Route::prefix('sales/invoice')->name('sales.invoice.')->group(function () {
    Route::get('/', [CustomerInvoiceController::class, 'index'])->name('index');
    Route::get('/create', [CustomerInvoiceController::class, 'create'])->name('create');
    Route::post('/', [CustomerInvoiceController::class, 'store'])->name('store');
    Route::get('/{id}', [CustomerInvoiceController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [CustomerInvoiceController::class, 'edit'])->name('edit');
    Route::put('/{id}', [CustomerInvoiceController::class, 'update'])->name('update');
    Route::delete('/{id}', [CustomerInvoiceController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/post', [CustomerInvoiceController::class, 'markAsPosted'])->name('post');
    Route::post('/{id}/pay', [CustomerInvoiceController::class, 'markAsPaid'])->name('pay');
    
    // TAMBAHKAN INI ↓
    Route::get('/source/items', [CustomerInvoiceController::class, 'getSourceItems'])->name('source.items');
});