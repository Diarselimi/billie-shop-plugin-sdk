<?php

declare(strict_types=1);

namespace App\Application\UseCase\LineItems;

/**
 * @OA\Schema(
 *     schema="LineItemsRequest",
 *     title="Order Line Item",
 *     required={"external_id", "quantity"},
 *     properties={
 *          @OA\Property(property="external_id", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="quantity", minimum=1, type="number")
 *     }
 * )
 */
class LineItemsRequest
{
}
