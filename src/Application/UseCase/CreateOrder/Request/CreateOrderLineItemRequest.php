<?php

namespace App\Application\UseCase\CreateOrder\Request;

use App\Support\NullableTaxedMoney;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="CreateOrderLineItemRequest",
 *     title="Order Line Item",
 *     required={"external_id", "title", "quantity", "amount_gross", "amount_tax", "amount_net"},
 *     properties={
 *          @OA\Property(property="external_id", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="title", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="description", type="string"),
 *          @OA\Property(property="quantity", minimum=1, type="number"),
 *          @OA\Property(property="category", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="brand", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="gtin", ref="#/components/schemas/TinyText", description="Global Trade Item Number"),
 *          @OA\Property(property="mpn", ref="#/components/schemas/TinyText", description="Manufacturer Part Numbers"),
 *          @OA\Property(property="amount", ref="#/components/schemas/AmountDTO"),
 *     }
 * )
 */
class CreateOrderLineItemRequest
{
    /**
     * @Assert\Type(type="string")
     * @Assert\NotBlank()
     */
    private $externalId;

    /**
     * @Assert\Type(type="string")
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @Assert\Type(type="string")
     */
    private $description;

    /**
     * @Assert\Type(type="int")
     * @Assert\GreaterThanOrEqual(value=1)
     * @Assert\NotBlank()
     */
    private $quantity;

    /**
     * @Assert\Type(type="string")
     */
    private $category;

    /**
     * @Assert\Type(type="string")
     */
    private $brand;

    /**
     * @Assert\Type(type="string")
     */
    private $gtin;

    /**
     * @Assert\Type(type="string")
     */
    private $mpn;

    /**
     * @Assert\Valid()
     */
    private $amount;

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId): CreateOrderLineItemRequest
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): CreateOrderLineItemRequest
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): CreateOrderLineItemRequest
    {
        $this->description = $description;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): CreateOrderLineItemRequest
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): CreateOrderLineItemRequest
    {
        $this->category = $category;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): CreateOrderLineItemRequest
    {
        $this->brand = $brand;

        return $this;
    }

    public function getGtin(): ?string
    {
        return $this->gtin;
    }

    public function setGtin(?string $gtin): CreateOrderLineItemRequest
    {
        $this->gtin = $gtin;

        return $this;
    }

    public function getMpn(): ?string
    {
        return $this->mpn;
    }

    public function setMpn(?string $mpn): CreateOrderLineItemRequest
    {
        $this->mpn = $mpn;

        return $this;
    }

    public function getAmount(): NullableTaxedMoney
    {
        return $this->amount;
    }

    public function setAmount(NullableTaxedMoney $amount): CreateOrderLineItemRequest
    {
        $this->amount = $amount;

        return $this;
    }
}
