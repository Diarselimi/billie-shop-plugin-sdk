<?php

namespace App\Application\UseCase\PauseOrderDunning;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class PauseOrderDunningRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $orderId;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     */
    private $merchantId;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @Assert\GreaterThan(value=0)
     */
    private $numberOfDays;

    public function __construct(string $orderId, int $merchantId, int $numberOfDays)
    {
        $this->orderId = $orderId;
        $this->merchantId = $merchantId;
        $this->numberOfDays = $numberOfDays;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getNumberOfDays(): int
    {
        return $this->numberOfDays;
    }
}
