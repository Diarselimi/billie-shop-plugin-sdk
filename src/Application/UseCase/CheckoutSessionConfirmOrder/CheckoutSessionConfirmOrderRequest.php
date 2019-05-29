<?php

namespace App\Application\UseCase\CheckoutSessionConfirmOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as CustomConstrains;
use Symfony\Component\Validator\Constraints as Assert;

class CheckoutSessionConfirmOrderRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\Valid()
     */
    private $amount;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="int")
     * @CustomConstrains\OrderDuration()
     */
    private $duration;

    public function getAmount(): ?CreateOrderAmountRequest
    {
        return $this->amount;
    }

    public function setAmount(?CreateOrderAmountRequest $amount): CheckoutSessionConfirmOrderRequest
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): CheckoutSessionConfirmOrderRequest
    {
        $this->duration = $duration;

        return $this;
    }
}
