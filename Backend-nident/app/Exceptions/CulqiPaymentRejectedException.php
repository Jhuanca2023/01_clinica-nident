<?php

namespace App\Exceptions;

use Exception;

class CulqiPaymentRejectedException extends Exception
{
    public function __construct(
        string $userMessage,
        protected string $errorCode = 'unknown',
        protected ?string $merchantMessage = null
    ) {
        parent::__construct($userMessage);
    }

    public function getUserMessage(): string
    {
        return $this->getMessage();
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getMerchantMessage(): ?string
    {
        return $this->merchantMessage;
    }
}
