<?php

declare(strict_types=1);

namespace App\Application\UseCase\ShipOrderWithInvoice;

use App\Application\UseCase\AbstractShipOrderRequestV1;
use App\DomainModel\ArrayableInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="ShipOrderWithInvoiceRequestV1", title="Order Shipping With Invoice Object", type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/AbstractShipOrderRequestV1")},
 *     properties={
 *      @OA\Property(property="invoice_file", description="Invoice file", type="string", format="binary")
 *     }
 * )
 */
class ShipOrderWithInvoiceRequestV1 extends AbstractShipOrderRequestV1 implements ArrayableInterface
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
