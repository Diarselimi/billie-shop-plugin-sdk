<?php

declare(strict_types=1);

namespace App\Application\UseCase\LineItems;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="LineItemsRequest",
 *     title="Order Line Item",
 *     required={"external_id", "quantity"},
 *     properties={
 *          @OA\Property(property="external_id", ref="#/components/schemas/TinyText", description="external identifier of the line item."),
 *          @OA\Property(property="quantity", minimum=1, type="number", description="The quantity of the items.")
 *     }
 * )
 */
class LineItemsRequest
{
    /**
     * @Assert\NotBlank
     */
    private ?string $externalId;

    /**
     * @Assert\NotBlank
     */
    private ?int $quantity;

    public function __construct(?string $externalId, ?int $quantity)
    {
        $this->externalId = $externalId;
        $this->quantity = $quantity;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }
}
