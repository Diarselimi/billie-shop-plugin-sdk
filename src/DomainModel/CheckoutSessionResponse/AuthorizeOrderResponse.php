<?php

namespace App\DomainModel\CheckoutSessionResponse;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="AuthorizeOrderResponse", title="Order Entity", type="object", properties={
 *
 *      @OA\Property(property="debtor_company", type="object", description="Identified company", properties={
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", example="Billie GmbH"),
 *          @OA\Property(property="address_house_number", ref="#/components/schemas/TinyText", nullable=true, example="4"),
 *          @OA\Property(property="address_street", ref="#/components/schemas/TinyText", example="Charlottenstr."),
 *          @OA\Property(property="address_postal_code", type="string", maxLength=5, example="10969"),
 *          @OA\Property(property="address_city", ref="#/components/schemas/TinyText", example="Berlin"),
 *          @OA\Property(property="address_country", type="string", maxLength=2),
 *      })
 * })
 */
class AuthorizeOrderResponse implements ArrayableInterface
{
    private $companyName;

    private $companyAddressHouseNumber;

    private $companyAddressStreet;

    private $companyAddressCity;

    private $companyAddressPostalCode;

    private $companyAddressCountry;

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $debtorCompanyName): AuthorizeOrderResponse
    {
        $this->companyName = $debtorCompanyName;

        return $this;
    }

    public function getCompanyAddressHouseNumber(): ?string
    {
        return $this->companyAddressHouseNumber;
    }

    public function setCompanyAddressHouseNumber(?string $companyAddressHouseNumber): AuthorizeOrderResponse
    {
        $this->companyAddressHouseNumber = $companyAddressHouseNumber;

        return $this;
    }

    public function getCompanyAddressStreet(): ?string
    {
        return $this->companyAddressStreet;
    }

    public function setCompanyAddressStreet(?string $companyAddressStreet): AuthorizeOrderResponse
    {
        $this->companyAddressStreet = $companyAddressStreet;

        return $this;
    }

    public function getCompanyAddressCity(): ?string
    {
        return $this->companyAddressCity;
    }

    public function setCompanyAddressCity(?string $companyAddressCity): AuthorizeOrderResponse
    {
        $this->companyAddressCity = $companyAddressCity;

        return $this;
    }

    public function getCompanyAddressPostalCode(): ?string
    {
        return $this->companyAddressPostalCode;
    }

    public function setCompanyAddressPostalCode(?string $companyAddressPostalCode): AuthorizeOrderResponse
    {
        $this->companyAddressPostalCode = $companyAddressPostalCode;

        return $this;
    }

    public function getCompanyAddressCountry(): ?string
    {
        return $this->companyAddressCountry;
    }

    public function setCompanyAddressCountry(?string $companyAddressCountry): AuthorizeOrderResponse
    {
        $this->companyAddressCountry = $companyAddressCountry;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'debtor_company' => [
                'name' => $this->getCompanyName(),
                'address_house_number' => $this->getCompanyAddressHouseNumber(),
                'address_street' => $this->getCompanyAddressStreet(),
                'address_postal_code' => $this->getCompanyAddressPostalCode(),
                'address_city' => $this->getCompanyAddressCity(),
                'address_country' => $this->getCompanyAddressCountry(),
            ],
        ];
    }
}
