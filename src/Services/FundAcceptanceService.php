<?php

namespace BDPay\LaravelBDPay\Services;

use BDPay\LaravelBDPay\Exceptions\BDPayException;

class FundAcceptanceService
{
    protected BDPayClient $client;

    public function __construct(BDPayClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create Virtual Account (VA).
     */
    public function createVA(array $data): array
    {
        $requiredFields = ['amount', 'order_id', 'customer_name', 'customer_email', 'customer_phone'];
        $this->validateRequiredFields($data, $requiredFields);

        $payload = [
            'merchantCode' => $this->client->getMerchantCode(),
            'method' => $data['bank_code'] ?? 'VA_BCA',
            'orderNum' => $data['order_id'],
            'payMoney' => (string) $data['amount'],
            'productDetail' => $data['description'] ?? 'Payment',
            'name' => $data['customer_name'],
            'email' => $data['customer_email'],
            'phone' => $data['customer_phone'],
            'notifyUrl' => $data['callback_url'] ?? $this->client->getConfig()['webhook']['payment_callback_url'],
            'expiryPeriod' => $data['expiry_period'] ?? '30',
            'dateTime' => date('c'), // ISO 8601 format
        ];

        return $this->client->request('POST', '/gateway/pay', $payload);
    }

    /**
     * Create Payment Link.
     */
    public function createPaymentLink(array $data): array
    {
        $requiredFields = ['amount', 'order_id', 'customer_name', 'customer_email', 'customer_phone'];
        $this->validateRequiredFields($data, $requiredFields);

        $payload = [
            'merchantCode' => $this->client->getMerchantCode(),
            'method' => $data['payment_method'] ?? null,
            'orderNum' => $data['order_id'],
            'payMoney' => (string) $data['amount'],
            'productDetail' => mb_substr($data['description'] ?? 'Payment', 0, 32),
            'name' => mb_substr($data['customer_name'], 0, 32),
            'email' => $data['customer_email'],
            'phone' => $data['customer_phone'],
            'notifyUrl' => $data['callback_url'] ?? $this->client->getConfig()['webhook']['payment_callback_url'],
            'expiryPeriod' => (string) ($data['expiry_period'] ?? '30'),
            'dateTime' => date('YmdHis'),
        ];

        return $this->client->request('POST', '/gateway/prepaidOrder', $payload);
    }

    /**
     * Create Static Virtual Account.
     */
    public function createStaticVA(array $data): array
    {
        $requiredFields = ['customer_name', 'customer_email', 'customer_phone'];
        $this->validateRequiredFields($data, $requiredFields);

        $payload = [
            'merchantCode' => $this->client->getMerchantCode(),
            'method' => $data['bank_code'] ?? 'VA_BCA',
            'name' => $data['customer_name'],
            'email' => $data['customer_email'],
            'phone' => $data['customer_phone'],
            'productDetail' => $data['description'] ?? 'Static VA',
            'notifyUrl' => $data['callback_url'] ?? $this->client->getConfig()['webhook']['payment_callback_url'],
            'dateTime' => date('c'), // ISO 8601 format
        ];

        return $this->client->request('POST', '/gateway/staticva/create', $payload);
    }

    /**
     * Get payment status.
     */
    public function getPaymentStatus(string $orderId): array
    {
        $payload = [
            'merchantCode' => $this->client->getMerchantCode(),
            'queryType' => 'ORDER_QUERY',
            'orderNum' => $orderId,
            'dateTime' => date('c'), // ISO 8601 format
        ];

        return $this->client->request('POST', '/gateway/pay/status', $payload);
    }

    /**
     * Validate required fields.
     */
    protected function validateRequiredFields(array $data, array $requiredFields): void
    {
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missingFields[] = $field;
            }
        }
        
        if (!empty($missingFields)) {
            throw new BDPayException(
                'Missing required fields: ' . implode(', ', $missingFields)
            );
        }
    }

    /**
     * Format amount to proper format (multiply by 100 for IDR).
     */
    public function formatAmount(float $amount, string $currency = 'IDR'): int
    {
        if ($currency === 'IDR') {
            return (int) ($amount * 100);
        }
        
        return (int) $amount;
    }

    /**
     * Parse amount from BDPay response.
     */
    public function parseAmount(int $amount, string $currency = 'IDR'): float
    {
        if ($currency === 'IDR') {
            return $amount / 100;
        }
        
        return (float) $amount;
    }
}
