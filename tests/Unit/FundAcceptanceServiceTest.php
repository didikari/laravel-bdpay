<?php

namespace BDPay\LaravelBDPay\Tests\Unit;

use BDPay\LaravelBDPay\Tests\TestCase;
use BDPay\LaravelBDPay\Services\FundAcceptanceService;
use BDPay\LaravelBDPay\Services\BDPayClient;
use BDPay\LaravelBDPay\Exceptions\BDPayException;
use Mockery;

class FundAcceptanceServiceTest extends TestCase
{
    protected FundAcceptanceService $service;
    protected $mockClient;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockClient = Mockery::mock(BDPayClient::class);
        $this->service = new FundAcceptanceService($this->mockClient);
    }

    public function test_can_format_amount_for_idr()
    {
        $this->assertEquals(100000, $this->service->formatAmount(1000.00, 'IDR'));
        $this->assertEquals(50000, $this->service->formatAmount(500.00, 'IDR'));
    }

    public function test_can_parse_amount_from_idr()
    {
        $this->assertEquals(1000.00, $this->service->parseAmount(100000, 'IDR'));
        $this->assertEquals(500.00, $this->service->parseAmount(50000, 'IDR'));
    }

    public function test_throws_exception_for_missing_required_fields_in_create_va()
    {
        $this->expectException(BDPayException::class);
        $this->expectExceptionMessage('Missing required fields');
        
        $this->service->createVA([]);
    }

    public function test_throws_exception_for_missing_required_fields_in_create_payment_link()
    {
        $this->expectException(BDPayException::class);
        $this->expectExceptionMessage('Missing required fields');
        
        $this->service->createPaymentLink([]);
    }

    public function test_throws_exception_for_missing_required_fields_in_create_static_va()
    {
        $this->expectException(BDPayException::class);
        $this->expectExceptionMessage('Missing required fields');
        
        $this->service->createStaticVA([]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
