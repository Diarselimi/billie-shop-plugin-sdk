<?php

namespace App\Application\UseCase\SaveMerchantFinancialAssessment;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="SaveMerchantFinancialAssessmentRequest", title="Request for submitting finnancial assessment",
 *     required={
 *      "yearly_transaction_volume",
 *      "mean_invoice_amount",
 *      "cancellation_rate",
 *      "invoice_duration",
 *      "default_rate",
 *      "high_invoice_amount",
 *      "digital_goods_rate"
 *     },
 *     properties={
 *      @OA\Property(property="yearly_transaction_volume", type="number", format="float", example=22.33),
 *      @OA\Property(property="mean_invoice_amount", type="number", format="float", example=233.44),
 *      @OA\Property(property="cancellation_rate", type="number", format="float", example=23.3),
 *      @OA\Property(property="invoice_duration", type="integer", example=100),
 *      @OA\Property(property="returning_order_rate", type="number", format="float", example=90.0, nullable=true),
 *      @OA\Property(property="default_rate", type="number", format="float", example=50.0),
 *      @OA\Property(property="high_invoice_amount", type="number", format="float", example=50.55),
 *      @OA\Property(property="digital_goods_rate", type="number", format="float", example=50.0),
 *     })
 */
class SaveMerchantFinancialAssessmentRequest implements ValidatedRequestInterface
{
    private $merchantId;

    private $merchantPaymentUuid;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex("/^\d+(\.\d{1,2})?$/", message="The number should have have maximum 2 numbers after decimal.")
     */
    private $yearlyTransactionVolume;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex("/^\d+(\.\d{1,2})?$/", message="The number should have have maximum 2 numbers after decimal.")
     */
    private $meanInvoiceAmount;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex("/^\d+(\.\d{1})?$/", message="The number should have have maximum 1 numbers after decimal.")
     */
    private $cancellationRate;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     */
    private $invoiceDuration;

    /**
     * @Assert\Regex("/^\d+(\.\d{1})?$/", message="The number should have have maximum 1 numbers after decimal.")
     */
    private $returningOrderRate;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex("/^\d+(\.\d{1})?$/", message="The number should have have maximum 1 numbers after decimal.")
     */
    private $defaultRate;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex("/^\d+(\.\d{1,2})?$/", message="The number should have have maximum 2 numbers after decimal.")
     */
    private $highInvoiceAmount;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex("/^\d+(\.\d{1})?$/", message="The number should have have maximum 1 numbers after decimal.")
     */
    private $digitalGoodsRate;

    public function __construct(int $merchantId, string $merchantPaymentUuid)
    {
        $this->merchantId = $merchantId;
        $this->merchantPaymentUuid = $merchantPaymentUuid;
    }

    public function getYearlyTransactionVolume(): float
    {
        return $this->yearlyTransactionVolume;
    }

    public function setYearlyTransactionVolume($yearlyTransactionVolume): SaveMerchantFinancialAssessmentRequest
    {
        $this->yearlyTransactionVolume = $yearlyTransactionVolume;

        return $this;
    }

    public function getMeanInvoiceAmount(): float
    {
        return $this->meanInvoiceAmount;
    }

    public function setMeanInvoiceAmount($meanInvoiceAmount): SaveMerchantFinancialAssessmentRequest
    {
        $this->meanInvoiceAmount = $meanInvoiceAmount;

        return $this;
    }

    public function getCancellationRate(): float
    {
        return $this->cancellationRate;
    }

    public function setCancellationRate($cancellationRate): SaveMerchantFinancialAssessmentRequest
    {
        $this->cancellationRate = $cancellationRate;

        return $this;
    }

    public function getInvoiceDuration(): int
    {
        return $this->invoiceDuration;
    }

    public function setInvoiceDuration($invoiceDuration): SaveMerchantFinancialAssessmentRequest
    {
        $this->invoiceDuration = $invoiceDuration;

        return $this;
    }

    public function getReturningOrderRate(): ?float
    {
        return $this->returningOrderRate;
    }

    public function setReturningOrderRate($returningOrderRate): SaveMerchantFinancialAssessmentRequest
    {
        $this->returningOrderRate = $returningOrderRate;

        return $this;
    }

    public function getDefaultRate(): ?float
    {
        return $this->defaultRate;
    }

    public function setDefaultRate($defaultRate): SaveMerchantFinancialAssessmentRequest
    {
        $this->defaultRate = $defaultRate;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getMerchantPaymentUuid(): string
    {
        return $this->merchantPaymentUuid;
    }

    public function getHighInvoiceAmount(): float
    {
        return $this->highInvoiceAmount;
    }

    public function setHighInvoiceAmount($highInvoiceAmount): SaveMerchantFinancialAssessmentRequest
    {
        $this->highInvoiceAmount = $highInvoiceAmount;

        return $this;
    }

    public function getDigitalGoodsRate(): float
    {
        return $this->digitalGoodsRate;
    }

    public function setDigitalGoodsRate($digitalGoodsRate): SaveMerchantFinancialAssessmentRequest
    {
        $this->digitalGoodsRate = $digitalGoodsRate;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'yearly_transaction_volume' => $this->getYearlyTransactionVolume(),
            'mean_invoice_amount' => $this->getMeanInvoiceAmount(),
            'cancellation_rate' => $this->getCancellationRate(),
            'invoice_duration' => $this->getInvoiceDuration(),
            'returning_order_rate' => $this->getReturningOrderRate(),
            'default_rate' => $this->getDefaultRate(),
            'high_invoice_amount' => $this->getHighInvoiceAmount(),
            'digital_goods_rate' => $this->getDigitalGoodsRate(),
        ];
    }
}
