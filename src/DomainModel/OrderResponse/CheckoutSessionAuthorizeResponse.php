<?php

namespace App\DomainModel\OrderResponse;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="CheckoutSessionAuthorizeResponse", title="Authorize Checkout Order Response", type="object",
 *     properties={
 *      @OA\Property(property="state", ref="#/components/schemas/OrderState", example="created"),
 *      @OA\Property(property="reasons", enum=\App\DomainModel\Order\OrderDeclinedReasonsMapper::REASONS, type="string", nullable=true, deprecated=true),
 *      @OA\Property(property="decline_reason", ref="#/components/schemas/OrderDeclineReason", nullable=true),
 *      @OA\Property(property="debtor_company", type="object", description="Identified company", properties={
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", nullable=true, example="Billie GmbH"),
 *          @OA\Property(property="address_house_number", ref="#/components/schemas/TinyText", nullable=true, example="4"),
 *          @OA\Property(property="address_street", ref="#/components/schemas/TinyText", nullable=true, example="Charlottenstr."),
 *          @OA\Property(property="address_postal_code", type="string", nullable=true, maxLength=5, example="10969"),
 *          @OA\Property(property="address_city", ref="#/components/schemas/TinyText", nullable=true, example="Berlin"),
 *          @OA\Property(property="address_country", type="string", nullable=true, maxLength=2),
 *      })
 * })
 */
class CheckoutSessionAuthorizeResponse implements ArrayableInterface
{
    private $state;

    private $companyName;

    private $companyAddressHouseNumber;

    private $companyAddressStreet;

    private $companyAddressCity;

    private $companyAddressPostalCode;

    private $companyAddressCountry;

    /**
     * @deprecated
     */
    private $reasons;

    private $declineReason;

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): CheckoutSessionAuthorizeResponse
    {
        $this->state = $state;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $debtorCompanyName): CheckoutSessionAuthorizeResponse
    {
        $this->companyName = $debtorCompanyName;

        return $this;
    }

    public function getCompanyAddressHouseNumber(): ?string
    {
        return $this->companyAddressHouseNumber;
    }

    public function setCompanyAddressHouseNumber(?string $companyAddressHouseNumber): CheckoutSessionAuthorizeResponse
    {
        $this->companyAddressHouseNumber = $companyAddressHouseNumber;

        return $this;
    }

    public function getCompanyAddressStreet(): ?string
    {
        return $this->companyAddressStreet;
    }

    public function setCompanyAddressStreet(?string $companyAddressStreet): CheckoutSessionAuthorizeResponse
    {
        $this->companyAddressStreet = $companyAddressStreet;

        return $this;
    }

    public function getCompanyAddressCity(): ?string
    {
        return $this->companyAddressCity;
    }

    public function setCompanyAddressCity(?string $companyAddressCity): CheckoutSessionAuthorizeResponse
    {
        $this->companyAddressCity = $companyAddressCity;

        return $this;
    }

    public function getCompanyAddressPostalCode(): ?string
    {
        return $this->companyAddressPostalCode;
    }

    public function setCompanyAddressPostalCode(?string $companyAddressPostalCode): CheckoutSessionAuthorizeResponse
    {
        $this->companyAddressPostalCode = $companyAddressPostalCode;

        return $this;
    }

    public function getCompanyAddressCountry(): ?string
    {
        return $this->companyAddressCountry;
    }

    public function setCompanyAddressCountry(?string $companyAddressCountry): CheckoutSessionAuthorizeResponse
    {
        $this->companyAddressCountry = $companyAddressCountry;

        return $this;
    }

    /**
     * @deprecated
     */
    public function getReasons(): ?array
    {
        return $this->reasons;
    }

    /**
     * @deprecated
     */
    public function setReasons(array $reasons): CheckoutSessionAuthorizeResponse
    {
        $this->reasons = $reasons;

        return $this;
    }

    public function getDeclineReason(): ?string
    {
        return $this->declineReason;
    }

    public function setDeclineReason(string $declineReason): CheckoutSessionAuthorizeResponse
    {
        $this->declineReason = $declineReason;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'state' => $this->getState(),
            'debtor_company' => [
                'name' => $this->getCompanyName(),
                'address_house_number' => $this->getCompanyAddressHouseNumber(),
                'address_street' => $this->getCompanyAddressStreet(),
                'address_postal_code' => $this->getCompanyAddressPostalCode(),
                'address_city' => $this->getCompanyAddressCity(),
                'address_country' => $this->getCompanyAddressCountry(),
            ],
            'reasons' => $this->getReasons() ? join(', ', $this->getReasons()) : null,
            'decline_reason' => $this->getDeclineReason(),
        ];
    }
}
