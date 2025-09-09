<?php

use Illuminate\Support\Facades\Route;
use BDPay\LaravelBDPay\Http\Controllers\WebhookController;
use BDPay\LaravelBDPay\Http\Middleware\VerifyBDPaySignature;

/*
|--------------------------------------------------------------------------
| BDPay Webhook Routes
|--------------------------------------------------------------------------
|
| These routes handle webhook callbacks from BDPay for payment and
| disbursement notifications.
|
*/

Route::prefix('bdpay/webhook')->group(function () {
    // Payment callback webhook
    Route::post('payment', [WebhookController::class, 'paymentCallback'])
        ->middleware(VerifyBDPaySignature::class)
        ->name('bdpay.webhook.payment');

    // Disbursement callback webhook
    Route::post('disbursement', [WebhookController::class, 'disbursementCallback'])
        ->middleware(VerifyBDPaySignature::class)
        ->name('bdpay.webhook.disbursement');
});
