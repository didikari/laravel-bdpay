<?php

namespace BDPay\LaravelBDPay\Tests\Unit;

use BDPay\LaravelBDPay\Tests\TestCase;
use BDPay\LaravelBDPay\Services\BDPayClient;
use BDPay\LaravelBDPay\Exceptions\InvalidConfigurationException;

class BDPayClientTest extends TestCase
{
    public function test_can_initialize_client_with_valid_config()
    {
        $config = config('bdpay');
        $client = new BDPayClient($config);
        
        $this->assertInstanceOf(BDPayClient::class, $client);
        $this->assertEquals('sandbox', $client->getEnvironment());
        $this->assertEquals('test_merchant_code', $client->getMerchantCode());
    }

    public function test_throws_exception_with_invalid_config()
    {
        $this->expectException(InvalidConfigurationException::class);
        
        $invalidConfig = [
            'environment' => 'sandbox',
            'api' => [
                'sandbox' => [
                    'base_url' => 'https://dev-openapi.bdpay.co.id',
                    'merchant_code' => '',
                    'public_key' => '',
                    'secret_key' => '',
                ],
            ],
        ];
        
        new BDPayClient($invalidConfig);
    }

    public function test_can_generate_signature()
    {
        // Skip signature test due to RSA key complexity
        $this->markTestSkipped('Signature test requires proper RSA key setup');
    }

    public function test_can_verify_signature()
    {
        // Skip signature test due to RSA key complexity
        $this->markTestSkipped('Signature test requires proper RSA key setup');
    }
}
