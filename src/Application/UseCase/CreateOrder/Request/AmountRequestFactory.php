<?php

namespace App\Application\UseCase\CreateOrder\Request;

class AmountRequestFactory
{
    public function createFromArray(?array $data): CreateOrderAmountRequest
    {
        return (new CreateOrderAmountRequest())
            ->setNet($data['net'] ?? null)
            ->setGross($data['gross'] ?? null)
            ->setTax($data['tax'] ?? null);
    }
}
