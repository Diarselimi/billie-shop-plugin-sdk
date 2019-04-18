<?php

namespace App\Application\UseCase\ShipOrder;

use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as CustomAssert;
use App\DomainModel\ArrayableInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ShipOrderRequest implements ValidatedRequestInterface, ArrayableInterface
{
    private $orderId;

    /**
     * @var string
     * @Assert\NotBlank(groups={"RequiredExternalCode"})
     * @Assert\Length(max="255")
     * @CustomAssert\OrderExternalCode()
     */
    private $externalOrderId;

    private $merchantId;

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

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): ShipOrderRequest
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getExternalCode(): ? string
    {
        return $this->externalOrderId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
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

    public function setMerchantId(?int $merchantId): ShipOrderRequest
    {
        $this->merchantId = $merchantId;

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
