<?php

declare(strict_types=1);

namespace App\Application\UseCase\ShipOrderWithInvoice;

use App\Application\UseCase\ValidatedRequestInterface;
use App\DomainModel\ArrayableInterface;
use App\DomainModel\ShipOrder\AbstractShipOrderRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="ShipOrderWithInvoiceRequest", title="Order Shipping With Invoice Object", type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/AbstractShipOrderRequest")},
 *     properties={
 *      @OA\Property(property="invoice_file", description="Invoice file", type="string", format="binary")
 *     }
 * )
 */
class ShipOrderWithInvoiceRequest extends AbstractShipOrderRequest implements ValidatedRequestInterface, ArrayableInterface
{
    /**
     * @Assert\NotBlank()
     */
    private $invoiceFile;

    public function getInvoiceFile(): UploadedFile
    {
        return $this->invoiceFile;
    }

    public function setInvoiceFile(UploadedFile $invoiceFile): self
    {
        $this->invoiceFile = $invoiceFile;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->getOrderId(),
            'merchant_id' => $this->getMerchantId(),
            'external_code' => $this->getExternalCode(),
            'invoice_number' => $this->getInvoiceNumber(),
        ];
    }
}
