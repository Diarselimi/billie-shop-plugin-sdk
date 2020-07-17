<?php

declare(strict_types=1);

namespace App\DomainModel\MerchantDebtorResponse;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="MerchantDebtorExternalIdsListResponse", type="object", properties={
 *      @OA\Property(property="total", type="integer", minimum=0),
 *      @OA\Property(property="items", type="array", @OA\Items(type="string", ref="#/components/schemas/TinyText"))
 * })
 */
class MerchantDebtorExternalIdsList implements ArrayableInterface
{
    private $total;

    private $items;

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'items' => array_map(function (array $item) {
                return $item['external_id'];
            }, $this->items),
        ];
    }
}
