<?php

namespace BDPay\LaravelBDPay\Exceptions;

use Exception;

class BDPayException extends Exception
{
    /**
     * The error code from BDPay API.
     */
    protected ?string $errorCode = null;

    /**
     * The error details from BDPay API.
     */
    protected ?array $errorDetails = null;

    /**
     * Create a new BDPay exception instance.
     */
    public function __construct(
        string $message = '',
        ?string $errorCode = null,
        ?array $errorDetails = null,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        
        $this->errorCode = $errorCode;
        $this->errorDetails = $errorDetails;
    }

    /**
     * Get the error code from BDPay API.
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * Get the error details from BDPay API.
     */
    public function getErrorDetails(): ?array
    {
        return $this->errorDetails;
    }

    /**
     * Get the error details as JSON string.
     */
    public function getErrorDetailsAsJson(): string
    {
        return json_encode($this->errorDetails ?? []);
    }
}
