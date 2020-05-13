<?php

namespace App\Http\RequestTransformer\CreateOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use Symfony\Component\HttpFoundation\Request;

class AddressRequestFactory
{
    public function create(Request $request, string $fieldName): ?CreateOrderAddressRequest
    {
        $data = $request->request->get($fieldName, []);

        if (empty($data)) {
            return null;
        }

        return  (new CreateOrderAddressRequest())
            ->setHouseNumber($data['house_number'] ?? null)
            ->setStreet($data['street'] ?? null)
            ->setPostalCode($data['postal_code'] ?? null)
            ->setCity($data['city'] ?? null)
            ->setCountry($data['country'] ?? null);
    }
}
