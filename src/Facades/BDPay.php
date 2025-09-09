<?php

namespace BDPay\LaravelBDPay\Facades;

use Illuminate\Support\Facades\Facade;
use BDPay\LaravelBDPay\Services\BDPayService;

/**
 * @method static \BDPay\LaravelBDPay\Services\FundAcceptanceService fundAcceptance()
 * @method static \BDPay\LaravelBDPay\Services\FundDisbursementService fundDisbursement()
 * @method static \BDPay\LaravelBDPay\Services\BDPayClient client()
 * @method static string generateSignature(array $data)
 * @method static bool verifySignature(string $signature, array $data)
 * @method static string getEnvironment()
 * @method static string getMerchantCode()
 */
class BDPay extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return BDPayService::class;
    }
}
