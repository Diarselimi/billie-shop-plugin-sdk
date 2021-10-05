<?php

declare(strict_types=1);

namespace App\Http\Response\PrivateApi;

use App\Application\UseCase\GetOrderCompact\GetOrderCompactResponse;
use App\DomainModel\Invoice\Invoice;
use OpenApi\Annotations as OA;
use Ozean12\Support\Formatting\DateFormat;
use Ozean12\Support\Serialization\ArrayableInterface;

/**
 * @OA\Schema(schema="GetOrderCompactResponse", type="object",
 *     properties={
 *          @OA\Property(property="order", type="object", nullable=false, properties={
 *              @OA\Property(property="uuid", ref="#/components/schemas/UUID", nullable=false),
 *              @OA\Property(property="external_code", ref="#/components/schemas/TinyText", nullable=true),
 *              @OA\Property(property="state", ref="#/components/schemas/OrderState", nullable=false),
 *              @OA\Property(property="amount", type="number", format="float", nullable=false),
 *              @OA\Property(property="workflow_name", ref="#/components/schemas/TinyText", nullable=false),
 *              @OA\Property(property="created_at", ref="#/components/schemas/DateTime", nullable=false),
 *          }),
 *          @OA\Property(property="invoices", type="object", nullable=false, properties={
 *              @OA\Property(property="uuid", ref="#/components/schemas/UUID", nullable=false),
 *              @OA\Property(property="payment_uuid", ref="#/components/schemas/UUID", nullable=false),
 *              @OA\Property(property="external_code", ref="#/components/schemas/TinyText", nullable=true),
 *              @OA\Property(property="state", ref="#/components/schemas/TinyText", nullable=false),
 *              @OA\Property(property="amount", type="number", format="float", nullable=false),
 *              @OA\Property(property="outstanding_amount", type="number", format="float", nullable=false),
 *              @OA\Property(property="duration", ref="#/components/schemas/OrderDuration", nullable=false),
 *              @OA\Property(property="due_date", ref="#/components/schemas/DateTime", nullable=false),
 *              @OA\Property(property="created_at", ref="#/components/schemas/DateTime", nullable=false),
 *          }),
 *          @OA\Property(property="merchant", type="object", nullable=false, properties={
 *              @OA\Property(property="company_uuid", ref="#/components/schemas/UUID", nullable=false),
 *              @OA\Property(property="payment_uuid", ref="#/components/schemas/UUID", nullable=true),
 *              @OA\Property(property="name", ref="#/components/schemas/TinyText", nullable=false),
 *          }),
 *          @OA\Property(property="debtor", type="object",
 *              description="If null, the debtor was not identified",
 *              nullable=true, properties={
 *              @OA\Property(property="uuid", ref="#/components/schemas/UUID", nullable=false),
 *              @OA\Property(property="company_uuid", ref="#/components/schemas/UUID", nullable=false),
 *              @OA\Property(property="payment_uuid", ref="#/components/schemas/UUID", nullable=true),
 *              @OA\Property(property="name", ref="#/components/schemas/TinyText", nullable=false),
 *          }),
 *          @OA\Property(property="buyer", type="object",
 *              description="Data of the person who bought the order in the checkout",
 *              nullable=false, properties={
 *              @OA\Property(property="first_name", ref="#/components/schemas/TinyText", nullable=true),
 *              @OA\Property(property="last_name", ref="#/components/schemas/TinyText", nullable=true),
 *              @OA\Property(property="email", type="string", format="email", nullable=false),
 *          }),
 *     })
 */
final class GetOrderCompactResponsePayload implements ArrayableInterface
{
    private GetOrderCompactResponse $useCaseResponse;

    public function __construct(GetOrderCompactResponse $useCaseResponse)
    {
        $this->useCaseResponse = $useCaseResponse;
    }

    public function toArray(): array
    {
        $container = $this->useCaseResponse->getOrderContainer();
        $order = $container->getOrder();
        $financialData = $container->getOrderFinancialDetails();

        return [
            'order' => [
                'uuid' => $order->getUuid(),
                'external_code' => $order->getExternalCode(),
                'state' => $order->getState(),
                'amount' => $financialData->getAmountGross()->getMoneyValue(),
                'workflow_name' => $order->getWorkflowName(),
                'created_at' => $order->getCreatedAt()->format(DateFormat::FORMAT_YMD_HIS),
            ],
            'invoices' => array_map([$this, 'transformInvoice'], array_values($container->getInvoices()->toArray())),
            'merchant' => [
                'company_uuid' => $container->getMerchant()->getCompanyUuid(),
                'payment_uuid' => $container->getMerchant()->getPaymentUuid(),
                'name' => $container->getMerchant()->getName(),
            ],
            'debtor' => $order->getMerchantDebtorId() ? [
                'uuid' => $container->getMerchantDebtor()->getUuid(),
                'company_uuid' => $container->getMerchantDebtor()->getCompanyUuid(),
                'payment_uuid' => $container->getMerchantDebtor()->getPaymentDebtorId(),
                'name' => $container->getDebtorCompany()->getName(),
            ] : null, // when null = debtor not identified
            'buyer' => [
                'first_name' => $container->getDebtorPerson()->getFirstName(),
                'last_name' => $container->getDebtorPerson()->getLastName(),
                'email' => $container->getDebtorPerson()->getEmail(),
            ],
        ];
    }

    private function transformInvoice(Invoice $invoice): array
    {
        return [
            'uuid' => $invoice->getUuid(),
            'payment_uuid' => $invoice->getPaymentUuid(),
            'external_code' => $invoice->getExternalCode(),
            'state' => $invoice->getState(),
            'amount' => $invoice->getAmount()->getGross()->getMoneyValue(),
            'outstanding_amount' => $invoice->getOutstandingAmount()->getMoneyValue(),
            'duration' => $invoice->getDuration(),
            'due_date' => $invoice->getDueDate()->format(DateFormat::FORMAT_YMD),
            'created_at' => $invoice->getCreatedAt()->format(DateFormat::FORMAT_YMD_HIS),
        ];
    }
}
