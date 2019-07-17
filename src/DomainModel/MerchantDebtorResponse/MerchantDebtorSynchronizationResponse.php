<?php

namespace App\DomainModel\MerchantDebtorResponse;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use OpenApi\Annotations as OA;

/**
 *
 * @OA\Schema(schema="MerchantDebtorSynchronizationResponse", title="Merchant Debtor Synchronization", type="object", properties={
 *      @OA\Property(property="debtor_company", type="object", description="Updated debtor company data", properties={
 *          @OA\Property(property="name", type="string", example="Some company"),
 *          @OA\Property(property="address_house_number", type="string", example="123"),
 *          @OA\Property(property="address_street", type="string", example="Random street"),
 *          @OA\Property(property="address_postal_code", type="string", example="10356"),
 *          @OA\Property(property="address_city", type="string", example="Berlin"),
 *          @OA\Property(property="address_country", type="string", example="Germany")
 *      }),
 *      @OA\Property(property="is_updated", type="boolean", example="true")
 * })
 */
class MerchantDebtorSynchronizationResponse implements ArrayableInterface
{
    private $debtorCompany;

    private $isUpdated;

    public function getDebtorCompany(): DebtorCompany
    {
        return $this->debtorCompany;
    }

    public function setDebtorCompany(DebtorCompany $debtorCompany): MerchantDebtorSynchronizationResponse
    {
        $this->debtorCompany = $debtorCompany;

        return $this;
    }

    public function getIsUpdated(): bool
    {
        return $this->isUpdated;
    }

    public function setIsUpdated(bool $isUpdated): MerchantDebtorSynchronizationResponse
    {
        $this->isUpdated = $isUpdated;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'debtor_company' => [
                'name' => $this->getDebtorCompany()->getName(),
                'address_house_number' => $this->getDebtorCompany()->getAddressHouse(),
                'address_street' => $this->getDebtorCompany()->getAddressStreet(),
                'address_postal_code' => $this->getDebtorCompany()->getAddressPostalCode(),
                'address_city' => $this->getDebtorCompany()->getAddressCity(),
                'address_country' => $this->getDebtorCompany()->getAddressCountry(),
            ],
            'is_updated' => $this->isUpdated,
        ];
    }
}
