<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\LocationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\BannerManagementController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::name('api.')->group(function () {
    Route::prefix('v1')->group(function () {
        Route::get('/', [\App\Http\Controllers\API\SwaggerController::class, 'welcome']);

        Route::prefix('m')->group(function () {
            Route::post('/register', [\App\Http\Controllers\API\Auth\RegisterController::class, 'register']);
            Route::post('/register-send-otp', [\App\Http\Controllers\API\Auth\RegisterController::class, 'registerSendOtp']);
            Route::post('/login', [AuthController::class, 'login']);
            Route::post('/verify-login', [AuthController::class, 'verifyLogin']);
            Route::post('/refresh-token', [AuthController::class, 'refresh']);
            Route::post('/lost-password', [AuthController::class, 'lostPassword']);

            Route::prefix('customer')->middleware([
                \App\Http\Middleware\API\VerifyAccessToken::class
            ])->group(function () {
                Route::get('/details', [CustomerController::class, 'getDetails']);
                Route::delete('/delete', [CustomerController::class, 'deleteAccount']);
                Route::post('/reset-password', [CustomerController::class, 'resetPassword']);
            });

            Route::prefix('wallet')->middleware([\App\Http\Middleware\API\VerifyAccessToken::class])->group(function () {
                Route::post('/topup', [WalletController::class, 'initiateTopUp'])->name('wallet.initiateTopUp');
                Route::post('/history/{type}', [WalletController::class, 'history'])->name('wallet.history');
            });

            Route::prefix('transaction')->middleware([\App\Http\Middleware\API\VerifyAccessToken::class])->group(function () {
                Route::post('/pay', [TransactionController::class, 'pay'])->name('transaction.pay');
            });

            Route::prefix('banner-management')->group(function () {
                Route::get('/', [BannerManagementController::class, 'index'])->name('banner-management.index');
                Route::post('/create', [BannerManagementController::class, 'store'])->name('banner-management.store');
                Route::post('/update', [BannerManagementController::class, 'update'])->name('banner-management.update');
                Route::post('/sort', [BannerManagementController::class, 'sort'])->name('banner-management.sort');
                Route::delete('/delete/{id}', [BannerManagementController::class, 'destroy'])->name('banner-management.destroy');
            });
        });
    });
});

Route::prefix('wallet')->group(function () {
    Route::post('/topup/callback', [WalletController::class, 'handleCallback'])->name('api.wallet.topupCallback');
    Route::get('/topup/success/{ref_number}', [WalletController::class, 'topupSuccess'])->name('api.wallet.topupSuccess');
});

Route::prefix('v1')->group(function () {
    Route::prefix('vm')->group(function () {
        Route::post('transaction/new', [TransactionController::class, 'newTransaction'])->name('api.transaction.new');
        Route::get('/transaction/get/{id}', [TransactionController::class, 'get'])->name('transaction.get');
    });

    Route::prefix('m')->group(function () {
        Route::get('/product/all', [ProductController::class, 'all'])->name('product.all');

        Route::prefix('location')->group(function () {
            Route::post('/create', [LocationController::class, 'create'])->name('product.create');
            Route::get('/all', [LocationController::class, 'all'])->name('product.all');
            Route::post('/toggle-status', [LocationController::class, 'toggleStatus'])->name('product.toggleStatus');
        });
    });
});
