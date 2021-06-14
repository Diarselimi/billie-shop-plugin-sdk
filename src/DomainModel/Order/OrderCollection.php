<?php

declare(strict_types=1);

namespace App\DomainModel\Order;

use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsCollection;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsNotFoundException;
use App\DomainModel\OrderInvoice\OrderInvoiceCollection;
use App\Support\ArrayCollection;

/**
 * @method OrderEntity[] toArray()
 * @method \ArrayIterator|OrderEntity[] getIterator()
 */
class OrderCollection extends ArrayCollection
{
    private array $orderIds;

    public function getIds(): array
    {
        if (isset($this->orderIds)) {
            return $this->orderIds;
        }

        $this->orderIds = array_map(
            static fn (OrderEntity $item) => $item->getId(),
            $this->items
        );

        return $this->orderIds;
    }

    public function assignLatestFinancialDetails(
        OrderFinancialDetailsCollection $financialDetailsEntityCollection
    ): void {
        $financialDetailsByOrderId = $financialDetailsEntityCollection->keySingleByOrderId();
        foreach ($this as $order) {
            if (!isset($financialDetailsByOrderId[$order->getId()])) {
                throw new OrderFinancialDetailsNotFoundException(
                    'Order financial details not found for order ' . $order->getId()
                );
            }

            $order->setLatestOrderFinancialDetails($financialDetailsByOrderId[$order->getId()]);
        }
    }

    public function assignOrderInvoices(
        OrderInvoiceCollection $orderInvoiceCollection
    ): void {
        $orderInvoicesByOrderId = $orderInvoiceCollection->keyByOrderId();
        foreach ($this as $order) {
            if (!isset($orderInvoicesByOrderId[$order->getId()])) {
                $order->setOrderInvoices(new OrderInvoiceCollection([]));

                continue;
            }

            $order->setOrderInvoices(
                new OrderInvoiceCollection($orderInvoicesByOrderId[$order->getId()])
            );
        }
    }
}
