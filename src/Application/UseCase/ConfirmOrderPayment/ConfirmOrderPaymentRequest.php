<?php

namespace App\Application\UseCase\ConfirmOrderPayment;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ConfirmOrderPaymentRequest implements ValidatedRequestInterface
{
    private $orderId;

    private $merchantId;

    /**
     * @Assert\GreaterThan(value=0)
     * @Assert\NotBlank()
     */
    private $paidAmount;

    public function __construct(string $orderId, int $merchantId, $amount)
    {
        $this->orderId = $orderId;
        $this->merchantId = $merchantId;
        $this->paidAmount = $amount;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getAmount(): float
    {
        return $this->paidAmount;
    }
}
