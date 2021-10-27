<?php

declare(strict_types=1);

namespace App\Http\Response\Dashboard;

use App\Application\UseCase\GetMerchantPaymentDetails\GetMerchantPaymentDetailsResponse;
use App\DomainModel\OrderInvoice\OrderInvoiceEntity;
use App\Http\Response\DTO\PaymentMethodDTO;
use OpenApi\Annotations as OA;
use Ozean12\Money\Money;
use Ozean12\Support\Formatting\DateFormat;
use Ozean12\Support\Serialization\ArrayableInterface;

/**
 * @OA\Schema(schema="GetMerchantPaymentDetailsResponse", title="Merchant Payment Details", type="object", properties={
 *   @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *   @OA\Property(property="amount", ref="#/components/schemas/Money"),
 *   @OA\Property(property="transaction_date", ref="#/components/schemas/Date", nullable=true),
 *   @OA\Property(property="is_allocated", type="boolean"),
 *   @OA\Property(property="overpaid_amount", ref="#/components/schemas/Money"),
 *   @OA\Property(property="merchant_debtor_uuid", ref="#/components/schemas/UUID", nullable=true),
 *   @OA\Property(property="transaction_counterparty_iban", ref="#/components/schemas/IBAN"),
 *   @OA\Property(property="transaction_counterparty_name", type="string"),
 *   @OA\Property(property="transaction_reference", type="string"),
 *   @OA\Property(property="invoices", type="array", @OA\Items(type="object", properties={
 *      @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="amount", ref="#/components/schemas/Money"),
 *      @OA\Property(property="mapped_amount", ref="#/components/schemas/Money"),
 *      @OA\Property(property="outstanding_amount", ref="#/components/schemas/Money"),
 *      @OA\Property(property="invoice_number", type="string", nullable=true),
 *      @OA\Property(property="order", type="array", @OA\Items(
 *          @OA\Property(property="uuid", type="#/components/schemas/UUID"),
 *          @OA\Property(property="external_id", type="string", nullable=true),
 *          @OA\Property(property="workflow_name", type="string", nullable=true)
 *     ))
 *   })),
 *   @OA\Property(property="payment_method", ref="#/components/schemas/PaymentMethod"),
 * })
 */
final class GetMerchantPaymentDetailsPayload implements ArrayableInterface
{
    private GetMerchantPaymentDetailsResponse $response;

    public function __construct(GetMerchantPaymentDetailsResponse $response)
    {
        $this->response = $response;
    }

    public function toArray(): array
    {
        $details = $this->response->getTransactionDetails();
        $totalMappedAmount = new Money(0);
        $invoices = array_map(
            function (OrderInvoiceEntity $orderInvoice) use (&$totalMappedAmount): array {
                $invoice = $orderInvoice->getInvoice();
                if ($orderInvoice->getInvoice() === null) {
                    return [];
                }

                $invoiceAmount = $invoice->getAmount()->getGross()->subtract($invoice->getCreditNotes()->getGrossSum());
                $mappedAmount = $invoiceAmount->subtract($invoice->getOutstandingAmount())->getMoneyValue();
                $totalMappedAmount = $totalMappedAmount->add($mappedAmount);

                return [
                    'uuid' => $invoice->getUuid(),
                    'amount' => $invoiceAmount->getMoneyValue(),
                    'mapped_amount' => $mappedAmount,
                    'outstanding_amount' => $orderInvoice->getInvoice()->getOutstandingAmount()->getMoneyValue(),
                    'invoice_number' => $orderInvoice->getInvoice()->getExternalCode(),
                    'order' => [
                        'uuid' => $orderInvoice->getOrder()->getUuid() ?? null,
                        'external_id' => $orderInvoice->getOrder()->getExternalCode() ?? null,
                        'workflow_name' => $orderInvoice->getOrder()->getWorkflowName() ?? null,
                    ],
                ];
            },
            $this->response->getOrderInvoicesCollection()->toArray()
        );

        $paymentMethod = $this->response->getPaymentMethod();

        return [
            'uuid' => $details->getUuid()->toString(),
            'transaction_date' => $details->getTransactionDate()
                ? $details->getTransactionDate()->format(DateFormat::FORMAT_YMD_HIS) : null,
            'amount' => $details->getAmount()->getMoneyValue(),
            'overpaid_amount' => $details->getOverPaidAmount()->subtract($totalMappedAmount)->getMoneyValue(),
            'is_allocated' => $details->isAllocated(),
            'merchant_debtor_uuid' => $details->getMerchantDebtorUuid()
                ? $details->getMerchantDebtorUuid()->toString() : null,
            'transaction_counterparty_iban' => $details->getCounterpartyIban(),
            'transaction_counterparty_name' => $details->getCounterpartyName(),
            'transaction_reference' => $details->getTransactionReference(),
            'invoices' => $invoices,
            'payment_method' => $paymentMethod ? (new PaymentMethodDTO($paymentMethod))->toArray() : null,
        ];
    }
}
