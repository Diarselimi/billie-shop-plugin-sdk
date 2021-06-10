<?php

declare(strict_types=1);

namespace App\DomainModel\OrderInvoice;

use App\Support\ArrayCollection;

/**
 * @method OrderInvoiceEntity[] toArray()
 */
class OrderInvoiceCollection extends ArrayCollection
{
    /**
     * @return string[]
     */
    public function getInvoiceUuids(): array
    {
        return array_map(static fn (OrderInvoiceEntity $entity) => $entity->getInvoiceUuid(), $this->toArray());
    }

    /**
     * @return OrderInvoiceEntity[][]
     */
    public function keyByInvoiceUuid(): array
    {
        return collect($this->items)
            ->reduce(
                function (array $carry, OrderInvoiceEntity $item) {
                    $invoiceUuid = $item->getInvoiceUuid();
                    if (!isset($items[$invoiceUuid])) {
                        $carry[$invoiceUuid] = [];
                    }
                    $carry[$invoiceUuid][] = $item;

                    return $carry;
                },
                []
            );
    }
}
