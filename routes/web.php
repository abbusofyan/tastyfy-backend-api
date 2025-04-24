<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\PublicUserController;
use App\Http\Controllers\CoPaymentUserController;
use App\Http\Controllers\BannerManagementController;
use App\Http\Controllers\BeneficiariesUserController;
use App\Http\Controllers\InstructionsController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\TopupLogController;
use App\Http\Controllers\TransactionLogController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['guest'])
    ->name('login.store');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', function () {
        return redirect()->route('publicUser.index');
    });
    Route::get('/dashboard', function () {
        // return Inertia::render('Dashboard');
        return redirect()->route('publicUser.index');
    })->name('dashboard');
    Route::controller(PublicUserController::class)->prefix('public-users')->group(function () {
        Route::get('/', 'index')->name('publicUser.index');
        Route::get('/fetch-data', 'fetchData')->name('publicUser.fetchData');
        // Put other Routes here...
    });
    Route::controller(BeneficiariesUserController::class)->group(function () {
        Route::get('/beneficiaries-users', 'index')->name('beneficiariesUser.index');
        Route::get('/beneficiaries-users/fetch-data', 'fetchData')->name('beneficiariesUser.fetchData');
        Route::patch('/beneficiaries-users/add-credit', 'addCredit')->name('beneficiariesUser.addCredit');
        Route::get('/beneficiaries-users/{id}', 'creditById')->name('beneficiariesUser.creditById');
        Route::post('/beneficiaries-users/import', 'import')->name('beneficiariesUser.import');
        // Put other Routes here...
    });
    Route::controller(CoPaymentUserController::class)->group(function () {
        Route::get('/co-payment-users', 'index')->name('coPaymentUser.index');
        Route::get('/co-payment-users/fetch-data', 'fetchData')->name('coPaymentUser.fetchData');
        Route::post('/co-payment-users/import', 'import')->name('coPaymentUser.import');
        // Put other Routes here...
    });
    Route::controller(AdminUserController::class)->group(function () {
        Route::get('/admin-users', 'index')->name('adminUser.index');
        Route::get('/admin-users/fetch-data', 'fetchData')->name('adminUser.fetchData');
        Route::put('/admin-users/update', 'update')->name('adminUser.update');
        Route::post('/admin-users/create', 'store')->name('adminUser.store');
        // Put other Route here...
    });

    Route::controller(CustomerController::class)->prefix('customers')->group(function () {
        Route::post('/store', 'store')->name('customer.store');
        Route::put('/update', 'update')->name('customer.update');
        Route::post('/add-credit', 'addCredit')->name('customer.addCredit');
        Route::post('/split-credit', 'splitCredit')->name('customer.splitCredit');
        Route::post('/import-credit', 'importCredit')->name('customer.importCredit');
    });


    Route::controller(BannerManagementController::class)->prefix('banner-management')->group(function () {
        Route::get('/', 'index')->name('banner-management.index');
        Route::get('/fetch', 'fetchData')->name('banner-management.fetch');
        Route::post('/create', 'store')->name('banner-management.store');
        Route::post('/update-order', 'updateOrder')->name('banner-management.updateOrder');
        Route::patch('/update', 'update')->name('banner-management.update');
        Route::post('/sort', 'sort')->name('banner-management.sort');
        Route::delete('/delete/{id}', 'destroy')->name('banner-management.destroy');
    });

    Route::controller(TopupLogController::class)->prefix('topup-log')->group(function () {
        Route::get('/', 'index')->name('topup-log.index');
        Route::get('/fetch', 'fetchData')->name('topup-log.fetch');
        Route::get('/export', 'exportToCSV')->name('topup-log.export');
    });

    Route::controller(TransactionLogController::class)->prefix('transaction-log')->group(function () {
        Route::get('/', 'index')->name('transaction-log.index');
        Route::get('/fetch', 'fetchData')->name('transaction-log.fetch');
        Route::get('/export', 'exportToCSV')->name('transaction-log.export');
    });

    // Route::controller(InstructionsController::class)->prefix('instructions')->group(function () {
    //     Route::get('/', 'index')->name('instructions.index');
    // });
});

Route::controller(InstructionsController::class)->prefix('instructions')->group(function () {
    Route::get('/', 'index')->name('instructions.index');
});
