<?php

use App\Http\Controllers\Api\ServiceCatalogApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/katalog', [ServiceCatalogApiController::class, 'index'])->name('api.katalog.index');
    Route::get('/katalog/{slugOrId}', [ServiceCatalogApiController::class, 'show'])->name('api.katalog.show');

    // API Pemesanan & Kalkulasi Biaya (Order Booking & Price Engine)
    Route::post('/orders/calculate-price', [\App\Http\Controllers\Api\OrderApiController::class, 'calculatePrice'])->name('api.orders.calculate-price');
    Route::post('/orders', [\App\Http\Controllers\Api\OrderApiController::class, 'store'])->name('api.orders.store');
    Route::get('/orders', [\App\Http\Controllers\Api\OrderApiController::class, 'index'])->name('api.orders.index');
    Route::get('/orders/{orderNumberOrId}', [\App\Http\Controllers\Api\OrderApiController::class, 'show'])->name('api.orders.show');
    Route::post('/orders/{orderNumberOrId}/cancel', [\App\Http\Controllers\Api\OrderApiController::class, 'cancel'])->name('api.orders.cancel');

    // API Payment Gateway & Escrow Integration
    Route::post('/payments/create-token', [\App\Http\Controllers\Api\PaymentApiController::class, 'createPaymentToken'])->name('api.payments.create-token');
    Route::post('/payments/webhook', [\App\Http\Controllers\Api\PaymentWebhookController::class, 'handleWebhook'])->name('api.payments.webhook');
    Route::post('/payments/simulate', [\App\Http\Controllers\Api\PaymentApiController::class, 'simulatePayment'])->name('api.payments.simulate');
    Route::get('/payments/{orderNumberOrId}', [\App\Http\Controllers\Api\PaymentApiController::class, 'show'])->name('api.payments.show');
});

