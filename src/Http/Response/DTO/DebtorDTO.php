<?php

declare(strict_types=1);

namespace App\Http\Response\DTO;

use App\DomainModel\ArrayableInterface;

/**
 * @OA\Schema(schema="Debtor", title="Order Debtor", type="object", properties={
 *      @OA\Property(property="name", type="string", example="C-10123456789-0001"),
 *      @OA\Property(property="company_address", ref="#/components/schemas/Address"),
 *      @OA\Property(property="billing_address", ref="#/components/schemas/Address"),
 *      @OA\Property(property="bank_account", ref="#/components/schemas/BankAccount", nullable=true),
 *      @OA\Property(property="external_data", ref="#/components/schemas/DebtorExternalData"),
 * })
 */
class DebtorDTO implements ArrayableInterface
{
    private ?string $name;

    private ?AddressDTO $companyAddressDTO;

    private AddressDTO $billingAddressDTO;

    private ?BankAccountDTO $bankAccountDTO;

    private DebtorExternalDataDTO $debtorExternalDataDTO;

    public function __construct(
        ?string $name,
        ?AddressDTO $companyAddress,
        AddressDTO $billingAddress,
        ?BankAccountDTO $bankAccount,
        DebtorExternalDataDTO $debtorExternalData
    ) {
        $this->name = $name;
        $this->companyAddressDTO = $companyAddress;
        $this->billingAddressDTO = $billingAddress;
        $this->bankAccountDTO = $bankAccount;
        $this->debtorExternalDataDTO = $debtorExternalData;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'company_address' => $this->companyAddressDTO ? $this->companyAddressDTO->toArray() : null,
            'billing_address' => $this->billingAddressDTO->toArray(),
            'bank_account' => $this->bankAccountDTO ? $this->bankAccountDTO->toArray() : null,
            'external_data' => $this->debtorExternalDataDTO->toArray(),
        ];
    }
}
