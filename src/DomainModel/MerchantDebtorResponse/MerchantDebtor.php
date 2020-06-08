<?php

namespace App\DomainModel\MerchantDebtorResponse;

use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="MerchantDebtorResponse", allOf={@OA\Schema(ref="#/components/schemas/AbstractMerchantDebtorResponse")}, type="object", properties={
 *      @OA\Property(property="address_street", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="address_house", ref="#/components/schemas/TinyText", nullable=true),
 *      @OA\Property(property="address_postal_code", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="address_city", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="address_country", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="outstanding_amount", type="number", format="float"),
 *      @OA\Property(property="outstanding_amount_created", type="number", format="float"),
 *      @OA\Property(property="outstanding_amount_late", type="number", format="float"),
 *      @OA\Property(property="debtor_information_change_request_state", ref="#/components/schemas/DebtorInformationChangeRequestState"),
 *      @OA\Property(property="debtor_information_change_request", type="array", @OA\Items(
 *          type="object",
 *          properties={
 *              @OA\Property(property="name", type="string"),
 *              @OA\Property(property="house_number", type="string"),
 *              @OA\Property(property="state", type="string"),
 *              @OA\Property(property="city", type="string"),
 *              @OA\Property(property="postal_code", type="string"),
 *          }
 *      )),
 *      @OA\Property(property="legal_form", ref="#/components/schemas/TinyText", nullable=true),
 * })
 */
class MerchantDebtor extends AbstractMerchantDebtor
{
    private $addressStreet;

    private $addressHouse;

    private $addressPostalCode;

    private $addressCity;

    private $addressCountry;

    private $outstandingAmount;

    private $outstandingAmountCreated;

    private $outstandingAmountLate;

    private $debtorInformationChangeRequest;

    private $legalForm;

    public function getAddressStreet(): string
    {
        return $this->addressStreet;
    }

    /**
     * @param  string                $addressStreet
     * @return MerchantDebtor|static
     */
    public function setAddressStreet(string $addressStreet): MerchantDebtor
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressHouse(): ? string
    {
        return $this->addressHouse;
    }

    /**
     * @param  string                $addressHouse
     * @return MerchantDebtor|static
     */
    public function setAddressHouse(?string $addressHouse): MerchantDebtor
    {
        $this->addressHouse = $addressHouse;

        return $this;
    }

    public function getAddressPostalCode(): string
    {
        return $this->addressPostalCode;
    }

    /**
     * @param  string                $addressPostalCode
     * @return MerchantDebtor|static
     */
    public function setAddressPostalCode(string $addressPostalCode): MerchantDebtor
    {
        $this->addressPostalCode = $addressPostalCode;

        return $this;
    }

    public function getAddressCity(): string
    {
        return $this->addressCity;
    }

    /**
     * @param  string                $addressCity
     * @return MerchantDebtor|static
     */
    public function setAddressCity(string $addressCity): MerchantDebtor
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressCountry(): string
    {
        return $this->addressCountry;
    }

    /**
     * @param  string                $addressCountry
     * @return MerchantDebtor|static
     */
    public function setAddressCountry(string $addressCountry): MerchantDebtor
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }

    public function getOutstandingAmount(): float
    {
        return $this->outstandingAmount;
    }

    /**
     * @param  float                 $outstandingAmount
     * @return MerchantDebtor|static
     */
    public function setOutstandingAmount(float $outstandingAmount): MerchantDebtor
    {
        $this->outstandingAmount = $outstandingAmount;

        return $this;
    }

    public function getOutstandingAmountCreated(): float
    {
        return $this->outstandingAmountCreated;
    }

    /**
     * @param  float                 $outstandingAmountCreated
     * @return MerchantDebtor|static
     */
    public function setOutstandingAmountCreated(float $outstandingAmountCreated): MerchantDebtor
    {
        $this->outstandingAmountCreated = $outstandingAmountCreated;

        return $this;
    }

    public function getOutstandingAmountLate(): float
    {
        return $this->outstandingAmountLate;
    }

    /**
     * @param  float                 $outstandingAmountLate
     * @return MerchantDebtor|static
     */
    public function setOutstandingAmountLate(float $outstandingAmountLate): MerchantDebtor
    {
        $this->outstandingAmountLate = $outstandingAmountLate;

        return $this;
    }

    public function setDebtorInformationChangeRequest(?DebtorInformationChangeRequestEntity $debtorInformationChangeRequest): MerchantDebtor
    {
        $this->debtorInformationChangeRequest = $debtorInformationChangeRequest;

        return $this;
    }

    public function getDebtorInformationChangeRequest(): ?DebtorInformationChangeRequestEntity
    {
        return $this->debtorInformationChangeRequest;
    }

    public function setLegalForm(?string $legalForm): MerchantDebtor
    {
        $this->legalForm = $legalForm;

        return $this;
    }

    public function getLegalForm(): ?string
    {
        return $this->legalForm;
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        return array_merge($data, [
            'address_street' => $this->addressStreet,
            'address_house' => $this->addressHouse,
            'address_postal_code' => $this->addressPostalCode,
            'address_city' => $this->addressCity,
            'address_country' => $this->addressCountry,

            'outstanding_amount' => $this->outstandingAmount,
            'outstanding_amount_created' => $this->outstandingAmountCreated,
            'outstanding_amount_late' => $this->outstandingAmountLate,

            'debtor_information_change_request' => $this->debtorInformationChangeRequest
                ? $this->getChangeRequestData($this->debtorInformationChangeRequest)
                : null,
            'legal_form' => $this->legalForm,
        ]);
    }

    private function getChangeRequestData(
        DebtorInformationChangeRequestEntity $debtorInformationChangeRequest
    ): ?array {
        $returnProperties = ['name', 'street', 'house_number', 'city', 'postal_code'];

        return
            $debtorInformationChangeRequest->getState() === DebtorInformationChangeRequestEntity::STATE_PENDING
                ? $debtorInformationChangeRequest->toArray($returnProperties)
                : null;
    }
}
