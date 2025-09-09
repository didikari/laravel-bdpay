<?php

namespace BDPay\LaravelBDPay\Exceptions;

class InvalidSignatureException extends BDPayException
{
    /**
     * Create a new invalid signature exception instance.
     */
    public function __construct(string $message = 'Invalid webhook signature')
    {
        parent::__construct($message);
    }
}
