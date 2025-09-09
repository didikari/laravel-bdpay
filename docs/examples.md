# BDPay Laravel Package - Usage Examples

This document provides comprehensive examples of how to use the BDPay Laravel package in various scenarios.

## Table of Contents

1. [Basic Setup](#basic-setup)
2. [Supported Payment Methods](#supported-payment-methods)
3. [Fund Acceptance Examples](#fund-acceptance-examples)
4. [Fund Disbursement Examples](#fund-disbursement-examples)
5. [Webhook Handling](#webhook-handling)
6. [Transaction Management](#transaction-management)
7. [Error Handling](#error-handling)
8. [Advanced Usage](#advanced-usage)

## Basic Setup

### 1. Installation and Configuration

```bash
# Install the package
composer require bdpay/laravel-bdpay

# Publish configuration
php artisan vendor:publish --provider="BDPay\LaravelBDPay\BDPayServiceProvider" --tag="bdpay-config"

# Publish migrations
php artisan vendor:publish --provider="BDPay\LaravelBDPay\BDPayServiceProvider" --tag="bdpay-migrations"

# Run migrations
php artisan migrate
```

### 2. Environment Configuration

```env
# .env
BDPAY_ENVIRONMENT=sandbox
BDPAY_SANDBOX_MERCHANT_CODE=your_sandbox_merchant_code
BDPAY_SANDBOX_PUBLIC_KEY=your_sandbox_public_key
BDPAY_SANDBOX_SECRET_KEY=your_sandbox_secret_key
BDPAY_PAYMENT_CALLBACK_URL=https://yourdomain.com/bdpay/webhook/payment
BDPAY_DISBURSEMENT_CALLBACK_URL=https://yourdomain.com/bdpay/webhook/disbursement
```

## Supported Payment Methods

### Virtual Account (VA)

- **VA_BCA** - BCA Bank Repayment Code
- **VA_MANDIRI** - Mandiri Bank Repayment Code
- **VA_PERMATA** - Permata Bank Repayment Code
- **VA_CIMB** - CIMB Bank Repayment Code
- **VA_BNI** - BNI Bank Repayment Code
- **VA_BRI** - BRI Bank Repayment Code
- **VA_BNC** - BNC Bank Repayment Code
- **VA_SAMPOERNA** - SAMPOERNA Bank Repayment Code
- **VA_MAYBANK** - Maybank Bank Repayment Code
- **VA_DANAMON** - Danamon Bank Repayment Code

### Retail Payment

- **RETAIL_ALFAMART** - Alfamart Convenience Store

### Digital Payment

- **QRIS** - QRIS scan code supports all electronic wallets, all kinds of bank scan code payment
- **EWALLET_DANA** - DANA Wallet
- **EWALLET_OVO** - OVO Electronic Wallet

### Other Features

- Payment Link (All payment methods)
- Static Virtual Account
- Disbursement (Bank Transfer)

## Fund Acceptance Examples

### 1. Create Virtual Account (VA)

```php
<?php

use BDPay\LaravelBDPay\Facades\BDPay;

class PaymentController extends Controller
{
    public function createVirtualAccount(Request $request)
    {
        try {
            $vaData = BDPay::fundAcceptance()->createVA([
                'amount' => 100000, // 100,000 IDR
                'order_id' => 'ORDER-' . time(),
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'description' => 'Payment for order #' . $request->order_id,
                'expired_at' => now()->addDays(1),
                'bank_code' => 'BCA', // Optional
                'currency' => 'IDR',
            ]);

            return response()->json([
                'success' => true,
                'data' => $vaData,
                'va_number' => $vaData['data']['va_number'] ?? null,
            ]);

        } catch (\BDPay\LaravelBDPay\Exceptions\BDPayException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
            ], 400);
        }
    }
}
```

### 2. Create Payment Link

```php
<?php

use BDPay\LaravelBDPay\Facades\BDPay;

class PaymentController extends Controller
{
    public function createPaymentLink(Request $request)
    {
        try {
            $paymentLinkData = BDPay::fundAcceptance()->createPaymentLink([
                'amount' => $request->amount,
                'order_id' => 'ORDER-' . time(),
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'description' => $request->description,
                'payment_method' => 'all', // or specific method
                'redirect_url' => route('payment.success'),
                'callback_url' => route('bdpay.webhook.payment'),
                'currency' => 'IDR',
            ]);

            return response()->json([
                'success' => true,
                'payment_link' => $paymentLinkData['data']['payment_link'] ?? null,
                'data' => $paymentLinkData,
            ]);

        } catch (\BDPay\LaravelBDPay\Exceptions\BDPayException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
```

### 3. Create Static Virtual Account

```php
<?php

use BDPay\LaravelBDPay\Facades\BDPay;

class CustomerController extends Controller
{
    public function createStaticVA(Request $request)
    {
        try {
            $staticVAData = BDPay::fundAcceptance()->createStaticVA([
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'description' => 'Static VA for ' . $request->customer_name,
                'bank_code' => 'BCA',
                'currency' => 'IDR',
            ]);

            return response()->json([
                'success' => true,
                'va_number' => $staticVAData['data']['va_number'] ?? null,
                'data' => $staticVAData,
            ]);

        } catch (\BDPay\LaravelBDPay\Exceptions\BDPayException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
```

### 4. Check Payment Status

```php
<?php

use BDPay\LaravelBDPay\Facades\BDPay;

class PaymentController extends Controller
{
    public function checkPaymentStatus($orderId)
    {
        try {
            $status = BDPay::fundAcceptance()->getPaymentStatus($orderId);

            return response()->json([
                'success' => true,
                'status' => $status['data']['status'] ?? 'unknown',
                'data' => $status,
            ]);

        } catch (\BDPay\LaravelBDPay\Exceptions\BDPayException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
```

## Fund Disbursement Examples

### 1. Create Disbursement

```php
<?php

use BDPay\LaravelBDPay\Facades\BDPay;

class DisbursementController extends Controller
{
    public function createDisbursement(Request $request)
    {
        try {
            // Note: Bank code validation is handled by BDPay API

            $disbursementData = BDPay::fundDisbursement()->createDisbursement([
                'amount' => $request->amount,
                'order_id' => 'DISBURSEMENT-' . time(),
                'recipient_name' => $request->recipient_name,
                'recipient_account' => $request->recipient_account,
                'bank_code' => $request->bank_code,
                'description' => $request->description,
                'callback_url' => route('bdpay.webhook.disbursement'),
                'currency' => 'IDR',
            ]);

            return response()->json([
                'success' => true,
                'data' => $disbursementData,
            ]);

        } catch (\BDPay\LaravelBDPay\Exceptions\BDPayException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
```

### 2. Check Disbursement Status

```php
<?php

use BDPay\LaravelBDPay\Facades\BDPay;

class DisbursementController extends Controller
{
    public function checkDisbursementStatus($orderId)
    {
        try {
            $status = BDPay::fundDisbursement()->getDisbursementStatus($orderId);

            return response()->json([
                'success' => true,
                'status' => $status['data']['status'] ?? 'unknown',
                'data' => $status,
            ]);

        } catch (\BDPay\LaravelBDPay\Exceptions\BDPayException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
```

### 3. Get Account Balance

```php
<?php

use BDPay\LaravelBDPay\Facades\BDPay;

class AccountController extends Controller
{
    public function getBalance()
    {
        try {
            $balance = BDPay::fundDisbursement()->getBalance();

            return response()->json([
                'success' => true,
                'balance' => $balance['data']['balance'] ?? 0,
                'currency' => $balance['data']['currency'] ?? 'IDR',
                'data' => $balance,
            ]);

        } catch (\BDPay\LaravelBDPay\Exceptions\BDPayException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
```

## Webhook Handling

### 1. Payment Webhook Controller

```php
<?php

namespace App\Http\Controllers;

use BDPay\LaravelBDPay\Http\Controllers\WebhookController as BDPayWebhookController;
use BDPay\LaravelBDPay\Models\BDPayTransaction;
use Illuminate\Http\Request;

class PaymentWebhookController extends BDPayWebhookController
{
    public function paymentCallback(Request $request)
    {
        // Call parent method to handle basic webhook processing
        $response = parent::paymentCallback($request);

        // Get the transaction
        $transaction = BDPayTransaction::where('order_id', $request->order_id)
            ->where('type', 'payment')
            ->first();

        if ($transaction && $transaction->isSuccessful()) {
            // Handle successful payment
            $this->handleSuccessfulPayment($transaction);
        }

        return $response;
    }

    protected function handleSuccessfulPayment(BDPayTransaction $transaction)
    {
        // Update order status
        // Send confirmation email
        // Update inventory
        // etc.
    }
}
```

### 2. Disbursement Webhook Controller

```php
<?php

namespace App\Http\Controllers;

use BDPay\LaravelBDPay\Http\Controllers\WebhookController as BDPayWebhookController;
use BDPay\LaravelBDPay\Models\BDPayTransaction;
use Illuminate\Http\Request;

class DisbursementWebhookController extends BDPayWebhookController
{
    public function disbursementCallback(Request $request)
    {
        // Call parent method to handle basic webhook processing
        $response = parent::disbursementCallback($request);

        // Get the transaction
        $transaction = BDPayTransaction::where('order_id', $request->order_id)
            ->where('type', 'disbursement')
            ->first();

        if ($transaction && $transaction->isSuccessful()) {
            // Handle successful disbursement
            $this->handleSuccessfulDisbursement($transaction);
        }

        return $response;
    }

    protected function handleSuccessfulDisbursement(BDPayTransaction $transaction)
    {
        // Update disbursement status
        // Send notification to recipient
        // Update accounting records
        // etc.
    }
}
```

## Transaction Management

### 1. Query Transactions

```php
<?php

use BDPay\LaravelBDPay\Models\BDPayTransaction;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = BDPayTransaction::query();

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($transactions);
    }

    public function show($orderId)
    {
        $transaction = BDPayTransaction::where('order_id', $orderId)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        return response()->json($transaction);
    }
}
```

### 2. Transaction Statistics

```php
<?php

use BDPay\LaravelBDPay\Models\BDPayTransaction;

class DashboardController extends Controller
{
    public function getStatistics()
    {
        $stats = [
            'total_payments' => BDPayTransaction::payments()->count(),
            'successful_payments' => BDPayTransaction::payments()->successful()->count(),
            'pending_payments' => BDPayTransaction::payments()->pending()->count(),
            'failed_payments' => BDPayTransaction::payments()->failed()->count(),
            'total_disbursements' => BDPayTransaction::disbursements()->count(),
            'successful_disbursements' => BDPayTransaction::disbursements()->successful()->count(),
            'total_amount' => BDPayTransaction::successful()->sum('amount'),
        ];

        return response()->json($stats);
    }
}
```

## Error Handling

### 1. Global Exception Handler

```php
<?php

namespace App\Exceptions;

use BDPay\LaravelBDPay\Exceptions\BDPayException;
use BDPay\LaravelBDPay\Exceptions\InvalidConfigurationException;
use BDPay\LaravelBDPay\Exceptions\InvalidSignatureException;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof BDPayException) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'error_code' => $exception->getErrorCode(),
                'error_details' => $exception->getErrorDetails(),
            ], 400);
        }

        if ($exception instanceof InvalidConfigurationException) {
            return response()->json([
                'success' => false,
                'message' => 'BDPay configuration error: ' . $exception->getMessage(),
            ], 500);
        }

        if ($exception instanceof InvalidSignatureException) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook signature verification failed',
            ], 401);
        }

        return parent::render($request, $exception);
    }
}
```

## Advanced Usage

### 1. Custom Service Class

```php
<?php

namespace App\Services;

use BDPay\LaravelBDPay\Facades\BDPay;
use BDPay\LaravelBDPay\Models\BDPayTransaction;

class PaymentService
{
    public function processPayment(array $paymentData)
    {
        try {
            // Create payment
            $payment = BDPay::fundAcceptance()->createVA($paymentData);

            // Store transaction
            $transaction = BDPayTransaction::create([
                'order_id' => $paymentData['order_id'],
                'transaction_id' => $payment['data']['transaction_id'] ?? null,
                'type' => 'payment',
                'status' => 'pending',
                'amount' => $paymentData['amount'],
                'currency' => $paymentData['currency'] ?? 'IDR',
                'customer_name' => $paymentData['customer_name'],
                'customer_email' => $paymentData['customer_email'],
                'customer_phone' => $paymentData['customer_phone'],
                'description' => $paymentData['description'],
                'request_data' => $paymentData,
                'response_data' => $payment,
            ]);

            return [
                'success' => true,
                'transaction' => $transaction,
                'va_number' => $payment['data']['va_number'] ?? null,
            ];

        } catch (\BDPay\LaravelBDPay\Exceptions\BDPayException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
            ];
        }
    }

    public function checkAndUpdatePaymentStatus($orderId)
    {
        try {
            $status = BDPay::fundAcceptance()->getPaymentStatus($orderId);

            $transaction = BDPayTransaction::where('order_id', $orderId)->first();
            if ($transaction) {
                $transaction->update([
                    'status' => $status['data']['status'] ?? 'pending',
                    'response_data' => $status,
                ]);
            }

            return $status;

        } catch (\BDPay\LaravelBDPay\Exceptions\BDPayException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
```

### 2. Queue Jobs for Webhook Processing

```php
<?php

namespace App\Jobs;

use BDPay\LaravelBDPay\Models\BDPayTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPaymentWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $transaction;

    public function __construct(BDPayTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function handle()
    {
        if ($this->transaction->isSuccessful()) {
            // Process successful payment
            $this->updateOrderStatus();
            $this->sendConfirmationEmail();
            $this->updateInventory();
        }
    }

    protected function updateOrderStatus()
    {
        // Update order status logic
    }

    protected function sendConfirmationEmail()
    {
        // Send email logic
    }

    protected function updateInventory()
    {
        // Update inventory logic
    }
}
```

### 3. Middleware for Webhook Protection

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use BDPay\LaravelBDPay\Facades\BDPay;
use BDPay\LaravelBDPay\Exceptions\InvalidSignatureException;

class VerifyBDPayWebhook
{
    public function handle(Request $request, Closure $next)
    {
        $signature = $request->header('X-BDPay-Signature');

        if (!$signature) {
            throw new InvalidSignatureException('Missing BDPay signature header');
        }

        if (!BDPay::verifySignature($signature, $request->all())) {
            throw new InvalidSignatureException('Invalid BDPay signature');
        }

        return $next($request);
    }
}
```

These examples should give you a comprehensive understanding of how to use the BDPay Laravel package in various scenarios. Feel free to adapt them to your specific needs!
