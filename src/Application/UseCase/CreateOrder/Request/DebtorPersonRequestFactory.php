<?php

namespace App\Application\UseCase\CreateOrder\Request;

class DebtorPersonRequestFactory
{
    public function createFromArray(?array $data): CreateOrderDebtorPersonRequest
    {
        return (new CreateOrderDebtorPersonRequest())
            ->setGender($data['salutation'] ?? null)
            ->setFirstName($data['first_name'] ?? null)
            ->setLastName($data['last_name'] ?? null)
            ->setPhoneNumber($data['phone_number'] ?? null)
            ->setEmail($data['email'] ?? null);
    }
}
