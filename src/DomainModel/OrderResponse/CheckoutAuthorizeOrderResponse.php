<?php

namespace App\DomainModel\OrderResponse;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\DebtorCompany\MostSimilarCandidateDTO;
use App\DomainModel\DebtorCompany\NullMostSimilarCandidateDTO;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="CheckoutAuthorizeOrderResponse", title="Authorize Checkout Order Response", type="object",
 *     properties={
 *      @OA\Property(property="state", ref="#/components/schemas/OrderState", example="created"),
 *      @OA\Property(property="decline_reason", ref="#/components/schemas/OrderDeclineReason", nullable=true),
 *      @OA\Property(property="debtor_company", type="object", description="Identified company", properties={
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", nullable=true, example="Billie GmbH"),
 *          @OA\Property(property="address_house_number", ref="#/components/schemas/TinyText", nullable=true, example="4"),
 *          @OA\Property(property="address_street", ref="#/components/schemas/TinyText", nullable=true, example="Charlottenstr."),
 *          @OA\Property(property="address_postal_code", type="string", nullable=true, maxLength=5, example="10969"),
 *          @OA\Property(property="address_city", ref="#/components/schemas/TinyText", nullable=true, example="Berlin"),
 *          @OA\Property(property="address_country", type="string", nullable=true, maxLength=2),
 *      }),
 *      @OA\Property(property="debtor_company_suggestion", type="object", description="Debtor company suggestion", properties={
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", nullable=true, example="Billie GmbH"),
 *          @OA\Property(property="address_house_number", ref="#/components/schemas/TinyText", nullable=true, example="4"),
 *          @OA\Property(property="address_street", ref="#/components/schemas/TinyText", nullable=true, example="Charlottenstr."),
 *          @OA\Property(property="address_postal_code", type="string", nullable=true, maxLength=5, example="10969"),
 *          @OA\Property(property="address_city", ref="#/components/schemas/TinyText", nullable=true, example="Berlin"),
 *          @OA\Property(property="address_country", type="string", nullable=true, maxLength=2),
 *      })
 * })
 */
class CheckoutAuthorizeOrderResponse implements ArrayableInterface
{
    private $state;

    private $companyName;

    private $companyAddressHouseNumber;

    private $companyAddressStreet;

    private $companyAddressCity;

    private $companyAddressPostalCode;

    private $companyAddressCountry;

    private $debtorCompanySuggestion;

    private $declineReason;

    public function __construct()
    {
        $this->debtorCompanySuggestion = new NullMostSimilarCandidateDTO();
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): CheckoutAuthorizeOrderResponse
    {
        $this->state = $state;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $debtorCompanyName): CheckoutAuthorizeOrderResponse
    {
        $this->companyName = $debtorCompanyName;

        return $this;
    }

    public function getCompanyAddressHouseNumber(): ?string
    {
        return $this->companyAddressHouseNumber;
    }

    public function setCompanyAddressHouseNumber(?string $companyAddressHouseNumber): CheckoutAuthorizeOrderResponse
    {
        $this->companyAddressHouseNumber = $companyAddressHouseNumber;

        return $this;
    }

    public function getCompanyAddressStreet(): ?string
    {
        return $this->companyAddressStreet;
    }

    public function setCompanyAddressStreet(?string $companyAddressStreet): CheckoutAuthorizeOrderResponse
    {
        $this->companyAddressStreet = $companyAddressStreet;

        return $this;
    }

    public function getCompanyAddressCity(): ?string
    {
        return $this->companyAddressCity;
    }

    public function setCompanyAddressCity(?string $companyAddressCity): CheckoutAuthorizeOrderResponse
    {
        $this->companyAddressCity = $companyAddressCity;

        return $this;
    }

    public function getCompanyAddressPostalCode(): ?string
    {
        return $this->companyAddressPostalCode;
    }

    public function setCompanyAddressPostalCode(?string $companyAddressPostalCode): CheckoutAuthorizeOrderResponse
    {
        $this->companyAddressPostalCode = $companyAddressPostalCode;

        return $this;
    }

    public function getCompanyAddressCountry(): ?string
    {
        return $this->companyAddressCountry;
    }

    public function setCompanyAddressCountry(?string $companyAddressCountry): CheckoutAuthorizeOrderResponse
    {
        $this->companyAddressCountry = $companyAddressCountry;

        return $this;
    }

    public function getDeclineReason(): ?string
    {
        return $this->declineReason;
    }

    public function setDeclineReason(string $declineReason): CheckoutAuthorizeOrderResponse
    {
        $this->declineReason = $declineReason;

        return $this;
    }

    public function getDebtorCompanySuggestion(): MostSimilarCandidateDTO
    {
        return $this->debtorCompanySuggestion;
    }

    public function setDebtorCompanySuggestion(MostSimilarCandidateDTO $debtorCompanySuggestion): CheckoutAuthorizeOrderResponse
    {
        $this->debtorCompanySuggestion = $debtorCompanySuggestion;

        return $this;
    }

    public function isAuthorized(): bool
    {
        return $this->state === OrderEntity::STATE_AUTHORIZED;
    }

    public function isDeclinedFinally(): bool
    {
        $finalDeclineReasons = [OrderContainer::DECLINE_REASON_ADDRESS_MISMATCH, OrderContainer::DECLINE_REASON_DEBTOR_NOT_IDENTIFIED];

        return $this->state === OrderEntity::STATE_DECLINED
            && !in_array($this->declineReason, $finalDeclineReasons, true)
        ;
    }

    public function toArray(): array
    {
        $debtorCompanySuggestion = $this->getDebtorCompanySuggestion() instanceof NullMostSimilarCandidateDTO
            ? null
            : $this->getDebtorCompanySuggestion()->toArray();

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
            'decline_reason' => $this->getDeclineReason(),
            'reasons' => $this->getDeclineReason(),
            'debtor_company_suggestion' => $debtorCompanySuggestion,
        ];
    }
}
