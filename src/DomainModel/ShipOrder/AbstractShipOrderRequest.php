<?php

namespace App\DomainModel\ShipOrder;

use App\Application\UseCase\AbstractOrderRequest;
use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as CustomAssert;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="AbstractShipOrderRequest", title="Order Shipping plain Object", type="object", properties={
 *      @OA\Property(property="external_order_id", ref="#/components/schemas/TinyText", nullable=true, description="External Order ID. It should only be provided if it was not provided in the create order call."),
 *      @OA\Property(property="invoice_number", ref="#/components/schemas/TinyText"),
 * })
 */
abstract class AbstractShipOrderRequest extends AbstractOrderRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank(groups={"RequiredExternalCode"})
     * @Assert\Length(max="255")
     * @CustomAssert\OrderExternalCode()
     */
    private $externalOrderId;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(max="255")
     */
    private $invoiceNumber;

    public function getExternalCode(): ?string
    {
        return $this->externalOrderId;
    }

    public function setExternalCode(?string $externalCode): self
    {
        $this->externalOrderId = $externalCode;

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
