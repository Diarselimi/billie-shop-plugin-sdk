<?php

namespace App\Application\UseCase\UpdateMerchantDebtorCompany;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="UpdateMerchantDebtorCompanyRequest",
 *     properties={
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", example="Billie GmbH"),
 *          @OA\Property(property="address_house", ref="#/components/schemas/TinyText", example="4"),
 *          @OA\Property(property="address_street", ref="#/components/schemas/TinyText", example="Charlottenstr."),
 *          @OA\Property(property="address_city", ref="#/components/schemas/TinyText", example="Berlin"),
 *          @OA\Property(property="address_postal_code", ref="#/components/schemas/TinyText", example="10969")
 *     }
 * )
 */
class UpdateMerchantDebtorCompanyRequest implements ValidatedRequestInterface
{
    private $debtorUuid;

    private $name;

    private $addressHouse;

    private $addressStreet;

    private $addressCity;

    private $addressPostalCode;

    public function getDebtorUuid(): string
    {
        return $this->debtorUuid;
    }

    public function setDebtorUuid(string $debtorUuid): UpdateMerchantDebtorCompanyRequest
    {
        $this->debtorUuid = $debtorUuid;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): UpdateMerchantDebtorCompanyRequest
    {
        $this->name = $name;

        return $this;
    }

    public function getAddressHouse(): ?string
    {
        return $this->addressHouse;
    }

    public function setAddressHouse(?string $addressHouse): UpdateMerchantDebtorCompanyRequest
    {
        $this->addressHouse = $addressHouse;

        return $this;
    }

    public function getAddressStreet(): ?string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet(?string $addressStreet): UpdateMerchantDebtorCompanyRequest
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressCity(): ?string
    {
        return $this->addressCity;
    }

    public function setAddressCity(?string $addressCity): UpdateMerchantDebtorCompanyRequest
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressPostalCode(): ?string
    {
        return $this->addressPostalCode;
    }

    public function setAddressPostalCode(?string $addressPostalCode): UpdateMerchantDebtorCompanyRequest
    {
        $this->addressPostalCode = $addressPostalCode;

        return $this;
    }
}
