<?php

namespace App\Application\UseCase\CreateOrder\Request;

class OrderLineItemsRequestFactory
{
    private $amountRequestFactory;

    public function __construct(AmountRequestFactory $amountRequestFactory)
    {
        $this->amountRequestFactory = $amountRequestFactory;
    }

    /**
     * @param array $lineItems
     *
     * @return CreateOrderLineItemRequest[]
     */
    public function createMultipleFromArray(array $lineItems): array
    {
        return array_map([$this, 'createFromArray'], $lineItems);
    }

    public function createFromArray(array $data): CreateOrderLineItemRequest
    {
        return (new CreateOrderLineItemRequest())
            ->setExternalId($data['external_id'] ?? null)
            ->setTitle($data['title'] ?? null)
            ->setDescription($data['description'] ?? null)
            ->setQuantity((int) $data['quantity'] ?? null)
            ->setCategory($data['category'] ?? null)
            ->setBrand($data['brand'] ?? null)
            ->setGtin($data['gtin'] ?? null)
            ->setMpn($data['mpn'] ?? null)
            ->setAmount($this->amountRequestFactory->createFromArray($data['amount'] ?? []))
        ;
    }
}
