<?php

namespace App\DomainModel\Invoice;

use App\Application\UseCase\CreateInvoice\CreateInvoiceRequest;
use App\Application\UseCase\ShipOrder\ShipOrderRequestV1;
use App\DomainModel\Fee\FeeCalculationException;
use App\DomainModel\Fee\FeeCalculatorInterface;
use App\DomainModel\Invoice\CreditNote\CreditNoteCollection;
use App\DomainModel\Invoice\CreditNote\CreditNoteFactory;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\Helper\Uuid\UuidGeneratorInterface;
use App\Infrastructure\Volt\VoltServiceException;
use App\Support\AbstractFactory;
use DateTime;
use Ozean12\Money\Money;
use Ozean12\Money\Percent;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ramsey\Uuid\Uuid;

class InvoiceFactory extends AbstractFactory
{
    private const INITIAL_INVOICE_STATE = 'new';

    private UuidGeneratorInterface $uuidGenerator;

    private FeeCalculatorInterface $feeCalculator;

    private CreditNoteFactory $creditNoteFactory;

    public function __construct(
        UuidGeneratorInterface $uuidGenerator,
        FeeCalculatorInterface $feeCalculator,
        CreditNoteFactory $creditNoteFactory
    ) {
        $this->uuidGenerator = $uuidGenerator;
        $this->feeCalculator = $feeCalculator;
        $this->creditNoteFactory = $creditNoteFactory;
    }

    public function create(
        OrderContainer $orderContainer,
        CreateInvoiceRequest $invoiceRequest
    ): Invoice {
        $duration = $orderContainer->getOrderFinancialDetails()->getDuration();
        $billingDate = new DateTime('today');
        $dueDate = new DateTime("today + {$duration} days");

        try {
            $fee = $this->feeCalculator->getCalculateFee(
                null,
                $invoiceRequest->getAmount()->getGross(),
                $billingDate,
                $dueDate,
                $orderContainer->getMerchantSettings()->getFeeRates()
            );
        } catch (VoltServiceException $exception) {
            throw new FeeCalculationException('Fee calculation call failed', 0, $exception);
        }

        $amount = $invoiceRequest->getAmount();

        return (new Invoice())
            ->setUuid($invoiceRequest->getInvoiceUuid()->toString())
            ->setCustomerUuid($orderContainer->getMerchant()->getPaymentUuid())
            ->setDebtorCompanyUuid($orderContainer->getMerchantDebtor()->getCompanyUuid())
            ->setPaymentDebtorUuid($orderContainer->getMerchantDebtor()->getPaymentDebtorId())
            ->setPaymentUuid($invoiceRequest->getInvoiceUuid()->toString())
            ->setAmount(clone $amount)
            ->setOutstandingAmount($amount->getGross())
            ->setPayoutAmount($amount->getGross()->subtract($fee->getGrossFeeAmount()))
            ->setFeeAmount(new TaxedMoney($fee->getGrossFeeAmount(), $fee->getNetFeeAmount(), $fee->getTaxFeeAmount()))
            ->setFeeRate($fee->getFeeRate())
            ->setDuration($duration)
            ->setState(self::INITIAL_INVOICE_STATE)
            ->setBillingDate($billingDate)
            ->setDueDate($dueDate)
            ->setCreatedAt(new \DateTime())
            ->setProofOfDeliveryUrl($invoiceRequest->getShippingDocumentUrl())
            ->setExternalCode($invoiceRequest->getExternalCode())
            ->setInvoicePendingCancellationAmount(new Money(0))
            ->setMerchantPendingPaymentAmount(new Money(0));
    }

    public function createFromArray(array $data): Invoice
    {
        $grossAmount = new Money($data['amount'], 0);
        $netAmount = new Money($data['amount_net'], 0);
        $taxAmount = new Money($data['amount_tax'], 0);

        $grossFeeAmount = new Money($data['fee_amount'], 0);
        $netFeeAmount = new Money($data['fee_amount_net'], 0);
        $taxFeeAmount = new Money($data['fee_amount_vat'], 0);

        $creditNotesData = $data['credit_notes'] ?? [];
        $creditNoteCollection = new CreditNoteCollection($this->creditNoteFactory->createFromArrayMultiple($creditNotesData));

        return (new Invoice())
            ->setUuid($data['uuid'])
            ->setPaymentUuid($data['payment_uuid'])
            ->setAmount((new TaxedMoney($grossAmount, $netAmount, $taxAmount)))
            ->setOutstandingAmount(new Money($data['outstanding_amount'], 0))
            ->setPayoutAmount(new Money($data['payout_amount'], 0))
            ->setFeeAmount(new TaxedMoney($grossFeeAmount, $netFeeAmount, $taxFeeAmount))
            ->setFeeRate(new Percent($data['factoring_fee_rate'], 0))
            ->setDuration($data['duration'])
            ->setCreatedAt(new DateTime($data['created_at']))
            ->setDueDate(new DateTime($data['due_date']))
            ->setDuration($data['duration'])
            ->setBillingDate(new DateTime($data['billing_date']))
            ->setProofOfDeliveryUrl('proof_of_delivery_url')
            ->setExternalCode($data['external_code'])
            ->setPaymentUuid($data['payment_uuid'])
            ->setState($data['state'])
            ->setCreatedAt(new DateTime($data['created_at']))
            ->setMerchantPendingPaymentAmount(new Money($data['merchant_pending_payment_amount'], 0))
            ->setInvoicePendingCancellationAmount(new Money($data['invoice_pending_cancellation_amount'], 0))
            ->setCreditNotes($creditNoteCollection);
    }

    public function createForShipment(
        OrderContainer $orderContainer,
        ShipOrderRequestV1 $request
    ): Invoice {
        $financialDetails = $orderContainer->getOrderFinancialDetails();
        $order = $orderContainer->getOrder();

        if ($order->getPaymentId() !== null) {
            $invoiceUuid = Uuid::fromString($order->getPaymentId());
        } else {
            $invoiceUuid = $this->uuidGenerator->uuid();
        }

        $input = new CreateInvoiceRequest($orderContainer->getMerchant()->getId(), $invoiceUuid);
        $input->setAmount(
            new TaxedMoney(
                $financialDetails->getAmountGross(),
                $financialDetails->getAmountNet(),
                $financialDetails->getAmountTax()
            )
        )
            ->setExternalCode($request->getInvoiceNumber())
            ->setShippingDocumentUrl($request->getShippingDocumentUrl());

        return $this->create($orderContainer, $input);
    }
}
