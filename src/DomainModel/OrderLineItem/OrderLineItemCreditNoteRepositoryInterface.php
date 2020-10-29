<?php

declare(strict_types=1);

namespace App\DomainModel\OrderLineItem;

interface OrderLineItemCreditNoteRepositoryInterface
{
    public function create(OrderLineItemCreditNoteEntity $creditNoteEntity): void;
}
