<?php

namespace BDPay\LaravelBDPay\Exceptions;

class InvalidConfigurationException extends BDPayException
{
    /**
     * Create a new invalid configuration exception instance.
     */
    public function __construct(string $message = 'Invalid BDPay configuration')
    {
        parent::__construct($message);
    }
}
