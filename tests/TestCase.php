<?php

namespace BDPay\LaravelBDPay\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use BDPay\LaravelBDPay\BDPayServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            BDPayServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('bdpay', [
            'environment' => 'sandbox',
            'api' => [
                'sandbox' => [
                    'base_url' => 'https://dev-openapi.bdpay.co.id',
                    'merchant_code' => 'test_merchant_code',
                    'public_key' => '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAt5fJh3yp+rCkA/aEMrgH
-----END PUBLIC KEY-----',
                    'secret_key' => '-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC3l8mHfKn6sKQD
9oQyuAd2vDjq09iAgZ8oXGMZVDSh0K4GR9nWA9Kpc5FR7iOKHKXY8+1DYGNbhUMw
-----END PRIVATE KEY-----',
                ],
                'production' => [
                    'base_url' => 'https://openapi.bdpay.co.id',
                    'merchant_code' => 'prod_merchant_code',
                    'public_key' => 'prod_public_key',
                    'secret_key' => 'prod_secret_key',
                ],
            ],
            'webhook' => [
                'payment_callback_url' => 'https://example.com/webhook/payment',
                'disbursement_callback_url' => 'https://example.com/webhook/disbursement',
                'verify_signature' => true,
            ],
            'defaults' => [
                'currency' => 'IDR',
                'timeout' => 30,
                'retry_attempts' => 3,
                'retry_delay' => 1000,
            ],
            'logging' => [
                'enabled' => true,
                'channel' => 'daily',
                'level' => 'info',
            ],
        ]);
    }
}
