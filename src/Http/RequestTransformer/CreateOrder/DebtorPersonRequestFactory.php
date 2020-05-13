<?php

namespace App\Http\RequestTransformer\CreateOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorPersonRequest;
use Symfony\Component\HttpFoundation\Request;

class DebtorPersonRequestFactory
{
    public function create(Request $request): CreateOrderDebtorPersonRequest
    {
        $data = $request->request->get('debtor_person', []);

        return (new CreateOrderDebtorPersonRequest())
            ->setGender($data['salutation'] ?? null)
            ->setFirstName($data['first_name'] ?? null)
            ->setLastName($data['last_name'] ?? null)
            ->setPhoneNumber($data['phone_number'] ?? null)
            ->setEmail($data['email'] ?? null);
    }
}
