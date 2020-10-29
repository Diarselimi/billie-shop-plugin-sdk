<?php

declare(strict_types=1);

namespace App\DomainModel\OrderLineItem;

use App\Support\AbstractFactory;

class OrderLineItemCreditNoteEntityFactory extends AbstractFactory
{
    public function createFromArray(array $data): OrderLineItemCreditNoteEntity
    {
        return (new OrderLineItemCreditNoteEntity())
            ->setId((int) $data['id'])
            ->setCreditNoteUuid($data['credit_note_uuid'])
            ->setOrderLineItemId((int) $data['order_line_item_id']);
    }

    public function create(int $orderLineItemId, string $creditNoteUuid): OrderLineItemCreditNoteEntity
    {
        return (new OrderLineItemCreditNoteEntity())
            ->setCreditNoteUuid($creditNoteUuid)
            ->setOrderLineItemId($orderLineItemId);
    }
}
