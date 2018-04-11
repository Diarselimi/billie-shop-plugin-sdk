<?php

namespace App\Application\UseCase\GetCustomer;

class GetCustomerResponse
{
    private $customerData;

    public function __construct(array $customerData)
    {
        $this->customerData = $customerData;
    }

    public function getCustomerData(): array
    {
        return $this->customerData;
    }
}
