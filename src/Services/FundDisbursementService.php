<?php

namespace BDPay\LaravelBDPay\Services;

use BDPay\LaravelBDPay\Exceptions\BDPayException;

class FundDisbursementService
{
    protected BDPayClient $client;

    public function __construct(BDPayClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create Disbursement.
     */
    public function createDisbursement(array $data): array
    {
        $requiredFields = ['amount', 'order_id', 'recipient_name', 'recipient_account', 'bank_code'];
        $this->validateRequiredFields($data, $requiredFields);

        $payload = [
            'merchantCode' => $this->client->getMerchantCode(),
            'orderNum' => $data['order_id'],
            'money' => (string) $data['amount'],
            'description' => $data['description'] ?? 'Disbursement',
            'name' => $data['recipient_name'],
            'bankCode' => $data['bank_code'],
            'number' => $data['recipient_account'],
            'notifyUrl' => $data['callback_url'] ?? $this->client->getConfig()['webhook']['disbursement_callback_url'],
            'feeType' => $data['fee_type'] ?? '0',
            'dateTime' => date('c'), // ISO 8601 format
        ];

        return $this->client->request('POST', '/gateway/cash', $payload);
    }

    /**
     * Get disbursement status.
     */
    public function getDisbursementStatus(string $orderId): array
    {
        $payload = [
            'merchantCode' => $this->client->getMerchantCode(),
            'orderNum' => $orderId,
            'dateTime' => date('c'), // ISO 8601 format
        ];

        return $this->client->request('POST', '/gateway/cash/status', $payload);
    }

    /**
     * Get account balance.
     */
    public function getBalance(): array
    {
        $payload = [
            'merchantCode' => $this->client->getMerchantCode(),
            'dateTime' => date('c'), // ISO 8601 format
        ];

        return $this->client->request('POST', '/gateway/account', $payload);
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
