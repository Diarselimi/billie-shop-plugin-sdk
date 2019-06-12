<?php

namespace App\Application\UseCase\ShipOrder;

use App\Application\UseCase\AbstractOrderRequest;
use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as CustomAssert;
use App\DomainModel\ArrayableInterface;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="ShipOrderRequest", title="Order Shipping Object", type="object", properties={
 *      @OA\Property(property="external_order_id", ref="#/components/schemas/TinyText", nullable=true),
 *      @OA\Property(property="invoice_number", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="invoice_url", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="proof_of_delivery_url", ref="#/components/schemas/TinyText")
 * })
 */
class ShipOrderRequest extends AbstractOrderRequest implements ValidatedRequestInterface, ArrayableInterface
{
    /**
     * @var string
     * @Assert\NotBlank(groups={"RequiredExternalCode"})
     * @Assert\Length(max="255")
     * @CustomAssert\OrderExternalCode()
     */
    private $externalOrderId;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $invoiceNumber;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $invoiceUrl;

    /**
     * @var string
     * @Assert\Length(max="255")
     */
    private $proofOfDeliveryUrl;

    public function getExternalCode(): ? string
    {
        return $this->externalOrderId;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function getInvoiceUrl(): string
    {
        return $this->invoiceUrl;
    }

    public function getProofOfDeliveryUrl(): ?string
    {
        return $this->proofOfDeliveryUrl;
    }

    public function setExternalCode(?string $externalCode): ShipOrderRequest
    {
        $this->externalOrderId = $externalCode;

        return $this;
    }

    public function setInvoiceNumber(?string $invoiceNumber): ShipOrderRequest
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function setInvoiceUrl(?string $invoiceUrl): ShipOrderRequest
    {
        $this->invoiceUrl = $invoiceUrl;

        return $this;
    }

    public function setProofOfDeliveryUrl(?string $proofOfDeliveryUrl): ShipOrderRequest
    {
        $this->proofOfDeliveryUrl = $proofOfDeliveryUrl;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->getOrderId(),
            'external_code' => $this->getExternalCode(),
            'merchant_id' => $this->getMerchantId(),
            'invoice_number' => $this->getInvoiceNumber(),
            'invoice_url' => $this->getInvoiceUrl(),
            'proof_of_delivery' => $this->getProofOfDeliveryUrl(),
        ];
    }
}
