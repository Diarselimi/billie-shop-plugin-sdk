<?php

namespace App\Http\RequestTransformer\CreateOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderLineItemRequest;
use App\Http\RequestTransformer\AmountRequestFactory;

class OrderLineItemsRequestFactory
{
    private AmountRequestFactory $amountRequestFactory;

    public function __construct(AmountRequestFactory $amountRequestFactory)
    {
        $this->amountRequestFactory = $amountRequestFactory;
    }

    /**
     * @return CreateOrderLineItemRequest[]
     */
    public function create(array $requestData): array
    {
        return array_map([$this, 'createFromArray'], $requestData);
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
            ->setAmount($this->amountRequestFactory->createNullableFromArray($data['amount'] ?? []));
    }
}
