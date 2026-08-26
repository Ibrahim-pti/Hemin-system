<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExternalJobController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockCountController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

// ── چوونەژوورەوە ───────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── سیستەم ─────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── داشبۆردی وەستاکان و دروستکردن ──
    Route::middleware('can:view_workshop')->group(function () {
        Route::get('/workshop', [\App\Http\Controllers\WorkshopController::class, 'dashboard'])->name('workshop.index');
        Route::get('/workshop/orders', [\App\Http\Controllers\WorkshopController::class, 'orders'])->name('workshop.orders');
        Route::get('/workshop/materials', [\App\Http\Controllers\WorkshopController::class, 'materials'])->name('workshop.materials');
        Route::get('/workshop/employees', [\App\Http\Controllers\WorkshopController::class, 'employees'])->name('workshop.employees');
        Route::post('/workshop/orders/{order}/status', [\App\Http\Controllers\WorkshopController::class, 'updateStatus'])->name('workshop.status');
        Route::post('/workshop/materials', [\App\Http\Controllers\WorkshopController::class, 'storeRawMaterial'])->name('workshop.store-material');
        Route::post('/workshop/stock-in', [\App\Http\Controllers\WorkshopController::class, 'stockIn'])->name('workshop.stock-in');
        Route::post('/workshop/stock-out', [\App\Http\Controllers\WorkshopController::class, 'stockOut'])->name('workshop.stock-out');
    });

    // ── مەخزەن ──
    Route::middleware('can:view_stock')->group(function () {
        Route::get('/items', [ItemController::class, 'index'])->name('items.index');
        Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
        Route::get('/stock', [StockMovementController::class, 'index'])->name('stock.index');
    });

    Route::middleware('can:manage_items')->group(function () {
        Route::get('/items-new', [ItemController::class, 'create'])->name('items.create');
        Route::post('/items', [ItemController::class, 'store'])->name('items.store');
        Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
        Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
        Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');

        Route::resource('categories', \App\Http\Controllers\CategoryController::class)->except('show');
        Route::resource('warehouses', WarehouseController::class)->except('show');
    });

    Route::middleware('can:manage_stock')->group(function () {
        Route::get('/stock/new', [StockMovementController::class, 'create'])->name('stock.create');
        Route::post('/stock', [StockMovementController::class, 'store'])->name('stock.store');
        Route::delete('/stock/{movement}', [StockMovementController::class, 'destroy'])->name('stock.destroy');
    });

    // ── جەرد ──
    Route::middleware('can:manage_stock_counts')->group(function () {
        Route::get('/counts', [StockCountController::class, 'index'])->name('counts.index');
        Route::post('/counts', [StockCountController::class, 'store'])->name('counts.store');
        Route::get('/counts/{count}', [StockCountController::class, 'show'])->name('counts.show');
        Route::put('/counts/{count}', [StockCountController::class, 'update'])->name('counts.update');
        Route::post('/counts/{count}/post', [StockCountController::class, 'post'])->name('counts.post');
        Route::delete('/counts/{count}', [StockCountController::class, 'destroy'])->name('counts.destroy');
    });

    // ── کڕین ──
    Route::middleware('can:manage_suppliers')->group(function () {
        Route::resource('suppliers', SupplierController::class);
    });

    Route::middleware('can:manage_purchases')->group(function () {
        Route::resource('purchases', PurchaseController::class);
        Route::post('/purchases/{purchase}/confirm', [PurchaseController::class, 'confirm'])->name('purchases.confirm');
        Route::post('/purchases/{purchase}/unconfirm', [PurchaseController::class, 'unconfirm'])->name('purchases.unconfirm');
    });

    // ── کڕیار و وەسڵ ──
    Route::middleware('can:manage_customers')->group(function () {
        Route::post('/customers/quick', [CustomerController::class, 'quickStore'])->name('customers.quick');
        Route::resource('customers', CustomerController::class);
        Route::get('/statement', [CustomerController::class, 'statementIndex'])->name('statement.index');
        Route::get('/customers/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement');
    });

    Route::middleware('can:manage_orders')->group(function () {
        Route::resource('orders', OrderController::class);
        Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
        Route::post('/orders/{order}/status', [OrderController::class, 'setStatus'])->name('orders.status');
    });

    Route::post('/units/quick', [ItemController::class, 'quickStoreUnit'])->name('units.quick');

    // ── پارە ──
    Route::middleware('can:manage_payments')->group(function () {
        Route::resource('payments', PaymentController::class)->except(['edit', 'update']);
        Route::get('/payments/{payment}/print', [PaymentController::class, 'print'])->name('payments.print');
        Route::get('/debts', [DebtController::class, 'index'])->name('debts.index');
        Route::post('/debts/old-debt', [DebtController::class, 'storeOldDebt'])->name('debts.old-debt');
    });

    Route::middleware('can:manage_cash')->group(function () {
        Route::get('/cash', [CashController::class, 'index'])->name('cash.index');
        Route::post('/cash/transaction', [CashController::class, 'storeTransaction'])->name('cash.transaction');
        Route::post('/cash/close', [CashController::class, 'close'])->name('cash.close');
    });

    // ── کار ──
    Route::middleware('can:manage_employees')->group(function () {
        Route::resource('employees', EmployeeController::class);
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/attendance/wages', [AttendanceController::class, 'wages'])->name('attendance.wages');
    });

    Route::middleware('can:manage_external_jobs')->group(function () {
        Route::resource('external-jobs', ExternalJobController::class)->parameters(['external-jobs' => 'job']);
    });

    // ── راپۆرت و ڕێکخستن ──
    Route::middleware('can:view_reports')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    });

    Route::middleware('can:manage_settings')->group(function () {
        Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/rate', [SettingController::class, 'storeRate'])->name('settings.rate');
        Route::post('/settings/sync-rate', [SettingController::class, 'syncLiveRate'])->name('settings.sync-rate');
        Route::post('/settings/backup', [SettingController::class, 'backup'])->name('settings.backup');
        Route::get('/settings/backup/{file}', [SettingController::class, 'download'])->name('settings.backup.download');
    });

    Route::get('/api/exchange-rate/live', [SettingController::class, 'liveRate'])->name('exchange-rate.live');
});
