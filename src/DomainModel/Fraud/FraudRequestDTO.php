<?php

declare(strict_types=1);

namespace App\DomainModel\Fraud;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\ArrayableInterface;
use App\DomainModel\OrderLineItem\OrderLineItemEntity;
use App\DomainModel\Person\PersonEntity;
use Ozean12\Money\Money;

class FraudRequestDTO implements ArrayableInterface
{
    private $invoiceUuid;

    private $merchantCompanyUuid;

    private $debtorCompanyUuid;

    private $person;

    private $isExistingCustomer;

    private $invoiceAmount;

    private $ipAddress;

    private $billingAddress;

    private $shippingAddress;

    private $invoiceCreatedAt;

    private $lineItems;

    private $debtorCompanySchufaId;

    public function __construct(
        string $invoiceUuid,
        string $merchantCompanyUuid,
        string $debtorCompanyUuid,
        PersonEntity $person,
        bool $isExistingCustomer,
        Money $invoiceAmount,
        ?string $ipAddress,
        AddressEntity $billingAddress,
        AddressEntity $shippingAddress,
        \DateTime $invoiceCreatedAt,
        array $lineItems,
        ?string $debtorCompanySchufaId = null
    ) {
        $this->invoiceUuid = $invoiceUuid;
        $this->merchantCompanyUuid = $merchantCompanyUuid;
        $this->debtorCompanyUuid = $debtorCompanyUuid;
        $this->person = $person;
        $this->isExistingCustomer = $isExistingCustomer;
        $this->invoiceAmount = $invoiceAmount;
        $this->ipAddress = $ipAddress;
        $this->billingAddress = $billingAddress;
        $this->shippingAddress = $shippingAddress;
        $this->invoiceCreatedAt = $invoiceCreatedAt;
        $this->lineItems = $lineItems;
        $this->debtorCompanySchufaId = $debtorCompanySchufaId;
    }

    public function toArray(): array
    {
        return [
            'invoice_uuid' => $this->invoiceUuid,
            'customer_company_uuid' => $this->merchantCompanyUuid,
            'debtor_company_uuid' => $this->debtorCompanyUuid,
            'debtor_schufa_id' => $this->debtorCompanySchufaId,
            'invoice_created_at' => $this->invoiceCreatedAt->format('Y-m-d H:i:s'),
            'email' => $this->person->getEmail(),
            'first_name' => $this->person->getFirstName(),
            'last_name' => $this->person->getLastName(),
            'phone_number' => $this->person->getPhoneNumber(),
            'is_existing_customer' => $this->isExistingCustomer,
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
            'line_items' => array_map(fn (OrderLineItemEntity $lineItem) => [
                'title' => $lineItem->getTitle(),
                'description' => $lineItem->getDescription(),
                'brand' => $lineItem->getBrand(),
            ], $this->lineItems),
        ];
    }
}
