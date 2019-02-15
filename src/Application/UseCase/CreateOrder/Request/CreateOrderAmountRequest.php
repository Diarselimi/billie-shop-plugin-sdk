<?php

namespace App\Application\UseCase\CreateOrder\Request;

use Symfony\Component\Validator\Constraints as Assert;
use App\Application\Validator\Constraint as CreateOrderUseCaseConstraints;

class CreateOrderAmountRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\GreaterThan(value=0)
     * @CreateOrderUseCaseConstraints\OrderAmount()
     */
    private $net;

    /**
     * @Assert\NotBlank()
     * @Assert\GreaterThan(value=0)
     * @CreateOrderUseCaseConstraints\OrderAmount()
     */
    private $gross;

    /**
     * @Assert\NotBlank()
     * @Assert\GreaterThanOrEqual(value=0)
     * @CreateOrderUseCaseConstraints\OrderAmount()
     */
    private $tax;

    public function getNet(): ?float
    {
        return $this->net;
    }

    public function setNet(?float $net): CreateOrderAmountRequest
    {
        $this->net = $net;

        return $this;
    }

    public function getGross(): ?float
    {
        return $this->gross;
    }

    public function setGross(?float $gross): CreateOrderAmountRequest
    {
        $this->gross = $gross;

        return $this;
    }

    public function getTax(): ?float
    {
        return $this->tax;
    }

    public function setTax(?float $tax): CreateOrderAmountRequest
    {
        $this->tax = $tax;

        return $this;
    }
}
