<?php

namespace App\DomainModel\OrderLineItem;

use App\Application\UseCase\CreateOrder\Request\CreateOrderLineItemRequest;

class OrderLineItemFactory
{
    public function createFromRequest(int $orderId, CreateOrderLineItemRequest $request): OrderLineItemEntity
    {
        return (new OrderLineItemEntity())
            ->setOrderId($orderId)
            ->setExternalId($request->getExternalId())
            ->setTitle($request->getTitle())
            ->setDescription($request->getDescription())
            ->setQuantity($request->getQuantity())
            ->setCategory($request->getCategory())
            ->setBrand($request->getBrand())
            ->setGtin($request->getGtin())
            ->setMpn($request->getMpn())
            ->setAmountGross($request->getAmount()->getGross())
            ->setAmountTax($request->getAmount()->getTax())
            ->setAmountNet($request->getAmount()->getNet())
        ;
    }

    /**
     * @param array $rows
     *
     * @return OrderLineItemEntity[]
     */
    public function createManyFromDatabaseRows(array $rows): array
    {
        return  array_map([$this, 'createFromDatabaseRow'], $rows);
    }

    public function createFromDatabaseRow(array $row): OrderLineItemEntity
    {
        return (new OrderLineItemEntity())
            ->setId(intval($row['id']))
            ->setOrderId(intval($row['order_id']))
            ->setExternalId($row['external_id'])
            ->setTitle($row['title'])
            ->setDescription($row['description'])
            ->setQuantity(intval($row['quantity']))
            ->setCategory($row['category'])
            ->setBrand($row['brand'])
            ->setGtin($row['gtin'])
            ->setMpn($row['mpn'])
            ->setAmountGross(floatval($row['amount_gross']))
            ->setAmountTax(floatval($row['amount_tax']))
            ->setAmountNet(floatval($row['amount_net']))
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
