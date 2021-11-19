<?php

namespace App\Application\UseCase;

use App\Application\Validator\Constraint as CustomAssert;
use App\DomainModel\Invoice\ShippingInfo;
use OpenApi\Annotations as OA;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="AbstractShipOrderRequestV1", title="Order Shipping plain Object", type="object", properties={
 *      @OA\Property(property="external_order_id", ref="#/components/schemas/TinyText", nullable=true, description="External Order ID. It should only be provided if it was not provided in the create order call."),
 *      @OA\Property(property="invoice_number", ref="#/components/schemas/TinyText"),
 * })
 */
abstract class AbstractShipOrderRequestV1 extends AbstractOrderRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank(groups={"RequiredExternalCode"})
     * @Assert\Type(type="string")
     * @Assert\Length(max="255")
     * @CustomAssert\OrderExternalCode()
     */
    private ?string $externalOrderId = null;

    /**
     * @Assert\NotBlank()
     * @CustomAssert\InvoiceExternalCode()
     * @Assert\Type(type="string")
     * @Assert\Length(max="255")
     */
    private ?string $invoiceNumber = null;

    private ?UuidInterface $invoiceUuid = null;

    private ?ShippingInfo $shippingInfo = null;

    public function getExternalCode(): ?string
    {
        return $this->externalOrderId;
    }

    public function setExternalCode($externalCode): self
    {
        $this->externalOrderId = $externalCode;

        return $this;
    }

    public function getInvoiceUuid(): ?UuidInterface
    {
        return $this->invoiceUuid;
    }

    public function setInvoiceUuid(?UuidInterface $invoiceUuid): AbstractShipOrderRequestV1
    {
        $this->invoiceUuid = $invoiceUuid;

        return $this;
    }

    public function getShippingInfo(): ?ShippingInfo
    {
        return $this->shippingInfo;
    }

    public function setShippingInfo(?ShippingInfo $shippingInfo): self
    {
        $this->shippingInfo = $shippingInfo;

        return $this;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): self
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }
}
