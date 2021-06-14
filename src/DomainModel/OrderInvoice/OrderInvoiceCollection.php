<?php

declare(strict_types=1);

namespace App\DomainModel\OrderInvoice;

use App\DomainModel\Invoice\InvoiceCollection;
use App\Support\ArrayCollection;

/**
 * @method OrderInvoiceEntity[] toArray()
 * @method \ArrayIterator|OrderInvoiceEntity[] getIterator()
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
    public function keyByOrderId(): array
    {
        return collect($this->items)
            ->reduce(
                function (array $carry, OrderInvoiceEntity $item) {
                    $orderId = $item->getOrderId();
                    if (!isset($items[$orderId])) {
                        $carry[$orderId] = [];
                    }
                    $carry[$orderId][] = $item;

                    return $carry;
                },
                []
            );
    }

    public function assignInvoices(
        InvoiceCollection $invoiceCollection
    ): void {
        $invoicesByUuid = $invoiceCollection->keyByUuid();
        foreach ($this as $orderInvoice) {
            if (!isset($invoicesByUuid[$orderInvoice->getInvoiceUuid()])) {
                $orderInvoice->setInvoice(null);

                continue;
            }

            $orderInvoice->setInvoice($invoicesByUuid[$orderInvoice->getInvoiceUuid()]);
        }
    }

    public function toInvoiceCollection(): InvoiceCollection
    {
        $invoices = array_map(
            static fn (OrderInvoiceEntity $orderInvoice) => $orderInvoice->getInvoice(),
            $this->toArray()
        );

        return new InvoiceCollection($invoices);
    }
}
