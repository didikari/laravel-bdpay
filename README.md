# Laravel BDPay Package

A comprehensive Laravel package for integrating with BDPay payment gateway, supporting both Fund Acceptance (payments) and Fund Disbursement features.

## Features

- **Fund Acceptance (Payments)**

  - Create Virtual Account (VA)
  - Create Payment Link
  - Create Static Virtual Account
  - Payment status checking
  - Payment webhook handling

- **Fund Disbursement**

  - Create disbursement transactions
  - Disbursement status checking
  - Account balance checking
  - Disbursement webhook handling

- **Additional Features**
  - Webhook signature verification
  - Comprehensive logging
  - Transaction tracking
- Multiple environment support (sandbox/production)
- Amount formatting utilities

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

## Installation

### 1. Install via Composer

```bash
composer require bdpay/laravel-bdpay
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --provider="BDPay\LaravelBDPay\BDPayServiceProvider" --tag="bdpay-config"
```

### 3. Publish Migrations

```bash
php artisan vendor:publish --provider="BDPay\LaravelBDPay\BDPayServiceProvider" --tag="bdpay-migrations"
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Publish Webhook Routes (Optional)

```bash
php artisan vendor:publish --provider="BDPay\LaravelBDPay\BDPayServiceProvider" --tag="bdpay-routes"
```

### 6. Configure Environment Variables

Add the following to your `.env` file:

```env
# BDPay Environment (sandbox or production)
BDPAY_ENVIRONMENT=sandbox

# Sandbox Configuration
BDPAY_SANDBOX_MERCHANT_CODE=your_sandbox_merchant_code
BDPAY_SANDBOX_PUBLIC_KEY=your_sandbox_public_key
BDPAY_SANDBOX_SECRET_KEY=your_sandbox_secret_key

# Production Configuration
BDPAY_PRODUCTION_MERCHANT_CODE=your_production_merchant_code
BDPAY_PRODUCTION_PUBLIC_KEY=your_production_public_key
BDPAY_PRODUCTION_SECRET_KEY=your_production_secret_key

# Webhook URLs
BDPAY_PAYMENT_CALLBACK_URL=https://yourdomain.com/bdpay/webhook/payment
BDPAY_DISBURSEMENT_CALLBACK_URL=https://yourdomain.com/bdpay/webhook/disbursement

# Optional Configuration
BDPAY_VERIFY_SIGNATURE=true
BDPAY_LOGGING_ENABLED=true
BDPAY_LOG_CHANNEL=daily
BDPAY_LOG_LEVEL=info
```

## Configuration

The package configuration is located in `config/bdpay.php`. You can customize various settings including:

- API endpoints for different environments
- Webhook verification settings
- Logging configuration
- Default currency and timeout settings

## Usage

### Basic Usage

```php
use BDPay\LaravelBDPay\Facades\BDPay;

// Create a Virtual Account
$vaData = BDPay::fundAcceptance()->createVA([
    'amount' => 100000, // Amount in IDR (100,000 IDR)
    'order_id' => 'ORDER-' . time(),
    'customer_name' => 'John Doe',
    'customer_email' => 'john@example.com',
    'customer_phone' => '081234567890',
    'description' => 'Payment for order #123',
    'expired_at' => now()->addDays(1),
    'bank_code' => 'BCA', // Optional
]);

// Create a Payment Link
$paymentLinkData = BDPay::fundAcceptance()->createPaymentLink([
    'amount' => 100000,
    'order_id' => 'ORDER-' . time(),
    'customer_name' => 'John Doe',
    'customer_email' => 'john@example.com',
    'customer_phone' => '081234567890',
    'description' => 'Payment for order #123',
    'payment_method' => 'all', // or specific payment method
    'redirect_url' => 'https://yourdomain.com/payment/success',
    'callback_url' => 'https://yourdomain.com/bdpay/webhook/payment',
]);

// Create a Static Virtual Account
$staticVAData = BDPay::fundAcceptance()->createStaticVA([
    'customer_name' => 'John Doe',
    'customer_email' => 'john@example.com',
    'customer_phone' => '081234567890',
    'description' => 'Static VA for customer',
    'bank_code' => 'BCA',
]);

// Check Payment Status
$status = BDPay::fundAcceptance()->getPaymentStatus('ORDER-123456');
```

### Fund Disbursement

```php
// Create Disbursement
$disbursementData = BDPay::fundDisbursement()->createDisbursement([
    'amount' => 50000,
    'order_id' => 'DISBURSEMENT-' . time(),
    'recipient_name' => 'Jane Doe',
    'recipient_account' => '1234567890',
    'bank_code' => 'BCA',
    'description' => 'Refund for order #123',
    'callback_url' => 'https://yourdomain.com/bdpay/webhook/disbursement',
]);

// Check Disbursement Status
$status = BDPay::fundDisbursement()->getDisbursementStatus('DISBURSEMENT-123456');

// Get Account Balance
$balance = BDPay::fundDisbursement()->getBalance();
```

### Webhook Handling

The package automatically handles webhook callbacks at:

- `/bdpay/webhook/payment` - For payment callbacks
- `/bdpay/webhook/disbursement` - For disbursement callbacks

Webhooks are automatically verified using signature validation. You can disable this in the configuration if needed.

### Transaction Tracking

All transactions are automatically stored in the `bdpay_transactions` table. You can query them using the `BDPayTransaction` model:

```php
use BDPay\LaravelBDPay\Models\BDPayTransaction;

// Get all successful payments
$successfulPayments = BDPayTransaction::payments()->successful()->get();

// Get pending disbursements
$pendingDisbursements = BDPayTransaction::disbursements()->pending()->get();

// Get transaction by order ID
$transaction = BDPayTransaction::where('order_id', 'ORDER-123')->first();
```

### Utility Methods

```php
// Format amount for BDPay API (multiply by 100 for IDR)
$formattedAmount = BDPay::fundAcceptance()->formatAmount(1000.00, 'IDR'); // Returns 100000

// Parse amount from BDPay response
$parsedAmount = BDPay::fundAcceptance()->parseAmount(100000, 'IDR'); // Returns 1000.00

// Or use disbursement service for disbursement amounts
$disbursementAmount = BDPay::fundDisbursement()->formatAmount(500.00, 'IDR'); // Returns 50000

// Note: Bank code validation is handled by BDPay API
// Use any bank code supported by BDPay (BCA, BNI, BRI, MANDIRI, etc.)

// Generate signature for webhook verification
$signature = BDPay::generateSignature($data);

// Verify webhook signature
$isValid = BDPay::verifySignature($signature, $data);
```

## Supported Banks

The package supports all banks that are supported by BDPay API. Bank code validation is handled automatically by the BDPay API, so you can use any bank code that BDPay supports.

Common Indonesian banks include:

- BCA, BNI, BRI, MANDIRI, CIMB, DANAMON, PERMATA, and many more

For the most up-to-date list of supported banks, please refer to the [BDPay API Documentation](https://document.bdpay.co.id/docs/api/).

## Error Handling

The package provides comprehensive error handling with custom exceptions:

```php
use BDPay\LaravelBDPay\Exceptions\BDPayException;
use BDPay\LaravelBDPay\Exceptions\InvalidConfigurationException;
use BDPay\LaravelBDPay\Exceptions\InvalidSignatureException;

try {
    $result = BDPay::fundAcceptance()->createVA($data);
} catch (BDPayException $e) {
    // Handle BDPay API errors
    echo $e->getMessage();
    echo $e->getErrorCode();
    echo $e->getErrorDetailsAsJson();
} catch (InvalidConfigurationException $e) {
    // Handle configuration errors
    echo $e->getMessage();
} catch (InvalidSignatureException $e) {
    // Handle webhook signature verification errors
    echo $e->getMessage();
}
```

## Logging

The package logs all API requests and responses when logging is enabled. Logs are written to the configured channel with the specified level.

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit
```

## API Documentation

For detailed API documentation, visit: [BDPay API Documentation](https://document.bdpay.co.id/docs/api/)

## Environment URLs

- **Sandbox**: `https://dev-openapi.bdpay.co.id`
- **Production**: `https://openapi.bdpay.co.id`

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For support, please contact:

- Email: support@bdpay.co.id
- Documentation: https://document.bdpay.co.id/docs/api/

## Changelog

### 1.0.0

- Initial release
- Fund Acceptance features (VA, Payment Link, Static VA)
- Fund Disbursement features
- Webhook handling with signature verification
- Transaction tracking
- Comprehensive logging
- Multiple environment support
