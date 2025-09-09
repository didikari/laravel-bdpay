<?php

namespace BDPay\LaravelBDPay\Services;

class BDPayService
{
    protected BDPayClient $client;
    protected FundAcceptanceService $fundAcceptance;
    protected FundDisbursementService $fundDisbursement;

    public function __construct(
        BDPayClient $client,
        FundAcceptanceService $fundAcceptance,
        FundDisbursementService $fundDisbursement
    ) {
        $this->client = $client;
        $this->fundAcceptance = $fundAcceptance;
        $this->fundDisbursement = $fundDisbursement;
    }

    /**
     * Get the fund acceptance service.
     */
    public function fundAcceptance(): FundAcceptanceService
    {
        return $this->fundAcceptance;
    }

    /**
     * Get the fund disbursement service.
     */
    public function fundDisbursement(): FundDisbursementService
    {
        return $this->fundDisbursement;
    }

    /**
     * Get the BDPay client.
     */
    public function client(): BDPayClient
    {
        return $this->client;
    }

    /**
     * Generate signature for webhook verification.
     */
    public function generateSignature(array $data): string
    {
        return $this->client->generateSignature($data);
    }

    /**
     * Verify webhook signature.
     */
    public function verifySignature(string $signature, array $data): bool
    {
        return $this->client->verifySignature($signature, $data);
    }

    /**
     * Get the current environment.
     */
    public function getEnvironment(): string
    {
        return $this->client->getEnvironment();
    }

    /**
     * Get the merchant code for current environment.
     */
    public function getMerchantCode(): string
    {
        return $this->client->getMerchantCode();
    }
}
