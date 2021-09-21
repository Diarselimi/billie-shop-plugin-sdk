<?php

namespace App\Http\RequestTransformer\CreateOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderLineItemRequest;
use App\Http\RequestTransformer\AmountRequestFactory;
use Symfony\Component\HttpFoundation\Request;

class OrderLineItemsRequestFactory
{
    private AmountRequestFactory $amountRequestFactory;

    public function __construct(AmountRequestFactory $amountRequestFactory)
    {
        $this->amountRequestFactory = $amountRequestFactory;
    }

    /**
     * @param  Request                      $request
     * @return CreateOrderLineItemRequest[]
     */
    public function create(Request $request): array
    {
        $lineItems = $request->request->get('line_items', []);

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
            ->setAmount($this->amountRequestFactory->createNullableFromArray($data['amount'] ?? []));
    }
}
