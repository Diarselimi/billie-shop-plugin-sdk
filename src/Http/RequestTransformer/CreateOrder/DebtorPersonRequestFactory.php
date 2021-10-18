<?php

namespace App\Http\RequestTransformer\CreateOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorPersonRequest;

class DebtorPersonRequestFactory
{
    public function create(array $requestData): CreateOrderDebtorPersonRequest
    {
        return (new CreateOrderDebtorPersonRequest())
            ->setGender($requestData['salutation'] ?? null)
            ->setFirstName($requestData['first_name'] ?? null)
            ->setLastName($requestData['last_name'] ?? null)
            ->setPhoneNumber($requestData['phone_number'] ?? null)
            ->setEmail($requestData['email'] ?? null)
        ;
    }
}
