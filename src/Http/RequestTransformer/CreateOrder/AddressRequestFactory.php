<?php

namespace App\Http\RequestTransformer\CreateOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use Symfony\Component\HttpFoundation\Request;

class AddressRequestFactory
{
    public function create(Request $request, string $fieldName): ?CreateOrderAddressRequest
    {
        $data = $request->request->get($fieldName);

        if (!is_array($data) || empty($data)) {
            return null;
        }

        return  (new CreateOrderAddressRequest())
            ->setHouseNumber($data['house_number'] ?? null)
            ->setStreet($data['street'] ?? null)
            ->setPostalCode($data['postal_code'] ?? null)
            ->setCity($data['city'] ?? null)
            ->setCountry(
                $this->normalizeCountry($data['country'] ?? null)
            );
    }

    /**
     * @deprecated don't use this for new endpoints, only existing ones
     */
    public function createFromOldFormat(array $requestData): CreateOrderAddressRequest
    {
        return (new CreateOrderAddressRequest())
        ->setAddition($requestData['address_addition'] ?? null)
        ->setHouseNumber($requestData['address_house_number'] ?? null)
        ->setStreet($requestData['address_street'] ?? null)
        ->setCity($requestData['address_city'] ?? null)
        ->setPostalCode($requestData['address_postal_code'] ?? null)
        ->setCountry(
            $this->normalizeCountry($requestData['address_country'] ?? null)
        );
    }

    private function normalizeCountry(?string $country): ?string
    {
        return $country === null ? null : strtoupper($country);
    }
}
