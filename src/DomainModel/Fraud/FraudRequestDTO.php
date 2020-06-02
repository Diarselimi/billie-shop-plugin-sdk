<?php

declare(strict_types=1);

namespace App\DomainModel\Fraud;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\ArrayableInterface;
use App\DomainModel\Person\PersonEntity;
use Ozean12\Money\Money;

class FraudRequestDTO implements ArrayableInterface
{
    private $invoiceUuid;

    private $person;

    private $isExistingCustomer;

    private $customerId;

    private $invoiceAmount;

    private $ipAddress;

    private $billingAddress;

    private $shippingAddress;

    public function __construct(
        string $invoiceUuid,
        PersonEntity $person,
        bool $isExistingCustomer,
        ?string $customerId,
        Money $invoiceAmount,
        ?string $ipAddress,
        AddressEntity $billingAddress,
        AddressEntity $shippingAddress
    ) {
        $this->invoiceUuid = $invoiceUuid;
        $this->person = $person;
        $this->isExistingCustomer = $isExistingCustomer;
        $this->customerId = $customerId;
        $this->invoiceAmount = $invoiceAmount;
        $this->ipAddress = $ipAddress;
        $this->billingAddress = $billingAddress;
        $this->shippingAddress = $shippingAddress;
    }

    public function toArray(): array
    {
        return [
            'invoice_uuid' => $this->invoiceUuid,
            'email' => $this->person->getEmail(),
            'first_name' => $this->person->getFirstName(),
            'last_name' => $this->person->getLastName(),
            'phone_number' => $this->person->getPhoneNumber(),
            'is_existing_customer' => $this->isExistingCustomer,
            'customer_id' => $this->customerId,
            'invoice_amount' => $this->invoiceAmount,
            'ip_address' => $this->ipAddress,
            'billing_address' => [
                'street' => $this->billingAddress->getStreet(),
                'house_number' => $this->billingAddress->getHouseNumber(),
                'postal_code' => $this->billingAddress->getPostalCode(),
                'city' => $this->billingAddress->getCity(),
                'country' => $this->billingAddress->getCountry(),
            ],
            'shipping_address' => [
                'street' => $this->shippingAddress->getStreet(),
                'house_number' => $this->shippingAddress->getHouseNumber(),
                'postal_code' => $this->shippingAddress->getPostalCode(),
                'city' => $this->shippingAddress->getCity(),
                'country' => $this->shippingAddress->getCountry(),
            ],
        ];
    }
}
