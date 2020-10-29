<?php

declare(strict_types=1);

namespace App\DomainModel\OrderLineItem;

use Billie\PdoBundle\DomainModel\AbstractEntity;

class OrderLineItemCreditNoteEntity extends AbstractEntity
{
    private int $orderLineItemId;

    private string $creditNoteUuid;

    public function getOrderLineItemId(): int
    {
        return $this->orderLineItemId;
    }

    public function setOrderLineItemId(int $orderLineItemId): self
    {
        $this->orderLineItemId = $orderLineItemId;

        return $this;
    }

    public function getCreditNoteUuid(): string
    {
        return $this->creditNoteUuid;
    }

    public function setCreditNoteUuid(string $creditNoteUuid): self
    {
        $this->creditNoteUuid = $creditNoteUuid;

        return $this;
    }
}
