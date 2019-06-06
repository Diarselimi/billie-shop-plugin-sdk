<?php

namespace App\DomainModel\MerchantDebtorResponse;

use App\DomainModel\ArrayableInterface;

class MerchantDebtorList implements ArrayableInterface
{
    /**
     * @var int
     */
    private $total;

    /**
     * @var MerchantDebtorListItem[]
     */
    private $items;

    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param  int                       $total
     * @return MerchantDebtorList|static
     */
    public function setTotal(int $total): MerchantDebtorList
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return MerchantDebtorListItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param  MerchantDebtorListItem[]  $items
     * @return MerchantDebtorList|static
     */
    public function setItems(MerchantDebtorListItem ...$items): MerchantDebtorList
    {
        $this->items = $items;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'items' => array_map(function (MerchantDebtorListItem $item) {
                return $item->toArray();
            }, $this->items),
        ];
    }
}
