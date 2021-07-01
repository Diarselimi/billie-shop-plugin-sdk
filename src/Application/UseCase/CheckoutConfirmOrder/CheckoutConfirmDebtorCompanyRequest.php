<?php

namespace App\Application\UseCase\CheckoutConfirmOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use App\DomainModel\ArrayableInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CheckoutConfirmDebtorCompanyRequest implements ArrayableInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private string $name;

    /**
     * @Assert\Valid()
     */
    private CreateOrderAddressRequest $companyAddress;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): CheckoutConfirmDebtorCompanyRequest
    {
        $this->name = $name;

        return $this;
    }

    public function getCompanyAddress(): CreateOrderAddressRequest
    {
        return $this->companyAddress;
    }

    public function setCompanyAddress(CreateOrderAddressRequest $companyAddress): CheckoutConfirmDebtorCompanyRequest
    {
        $this->companyAddress = $companyAddress;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'company_address' => $this->getCompanyAddress()->toArray(),
        ];
    }
}
