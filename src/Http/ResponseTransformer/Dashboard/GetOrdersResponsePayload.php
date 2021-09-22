<?php

declare(strict_types=1);

namespace App\Http\ResponseTransformer\Dashboard;

use App\Application\UseCase\Dashboard\GetOrders\GetOrdersResponse;
use App\DomainModel\ArrayableInterface;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderEntity;
use App\Support\DateFormat;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="GetOrdersResponsePayloadInvoice", type="object", properties={
 *      @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="invoice_number", type="string", nullable=false, example="O-10123456789-0001", description="Invoice Number"),
 *      @OA\Property(property="created_at", nullable=false, ref="#/components/schemas/DateTime"),
 *      @OA\Property(property="due_date", nullable=false, ref="#/components/schemas/DateTime"),
 *      @OA\Property(property="amount", type="number", format="float", nullable=false, example=10.00, description="Gross amount"),
 *      @OA\Property(property="outstanding_amount", type="number", format="float", nullable=false, example=10.00),
 * })
 *
 * @OA\Schema(schema="GetOrdersResponsePayload", type="object", properties={
 *      @OA\Property(property="total", type="number", nullable=false, example=42500),
 *      @OA\Property(property="items", type="array", nullable=false,
 *          @OA\Items(
 *              type="object",
 *              properties={
 *                  @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *                  @OA\Property(property="order_id", type="string", nullable=true, example="O-10123456789", description="Order Number"),
 *                  @OA\Property(property="created_at", nullable=false, ref="#/components/schemas/DateTime"),
 *                  @OA\Property(property="state", nullable=false, ref="#/components/schemas/OrderState", example="created"),
 *                  @OA\Property(property="duration", ref="#/components/schemas/OrderDuration", example=30),
 *                  @OA\Property(property="amount", type="number", format="float", nullable=false, example=120.00, description="Gross amount"),
 *                  @OA\Property(property="invoice", nullable=true, deprecated=true, ref="#/components/schemas/GetOrdersResponsePayloadInvoice"),
 *                  @OA\Property(property="invoices", nullable=false,
 *                      type="array",
 *                      @OA\Items(ref="#/components/schemas/GetOrdersResponsePayloadInvoice")
 *                  ),
 *                  @OA\Property(property="workflow_name", type="string", nullable=false, example="order_v1", description="Workflow Name"),
 *              }
 *          )
 *      ),
 * })
 */
class GetOrdersResponsePayload implements ArrayableInterface
{
    private GetOrdersResponse $response;

    public function __construct(GetOrdersResponse $response)
    {
        $this->response = $response;
    }

    public function toArray(): array
    {
        return [
            'total' => $this->response->getTotalCount(),
            'items' => array_map(
                function (OrderEntity $order) {
                    return $this->transformOrder($order);
                },
                $this->response->getOrders()->toArray()
            ),
        ];
    }

    private function transformOrder(OrderEntity $order): array
    {
        $financialData = $order->getLatestOrderFinancialDetails();
        $invoices = array_map(
            function (Invoice $invoice) {
                return $this->transformInvoice($invoice);
            },
            array_values($order->getOrderInvoices()->toInvoiceCollection()->toArray())
        );
        $invoice = empty($invoices) ? $this->getInvoiceFallback() : $invoices[0];
        $orderDueDate = new \DateTime();
        $orderDueDate->setTimestamp($order->getCreatedAt()->getTimestamp());
        $orderDueDate->modify("+ {$financialData->getDuration()} days");
        $orderDueDateStr = $orderDueDate->format(DateFormat::FORMAT_YMD);
        $invoice['due_date'] = $orderDueDateStr; // to keep backwards compat.

        return [
            'uuid' => $order->getUuid(),
            'order_id' => $order->getExternalCode(),
            'created_at' => $order->getCreatedAt()->format(DateFormat::FORMAT_YMD_HIS),
            'state' => $order->getState(),
            'duration' => $financialData->getDuration(),
            'due_date' => $orderDueDateStr,
            'amount' => $financialData->getAmountGross()->getMoneyValue(),
            'invoice' => $invoice, // TODO: remove when dashboard UI stops using it
            'invoices' => $invoices,
            'workflow_name' => $order->getWorkflowName(),
        ];
    }

    private function getInvoiceFallback(): array
    {
        return [
            'uuid' => null,
            'invoice_number' => null,
            'created_at' => null,
            'due_date' => null,
            'amount' => null,
            'outstanding_amount' => null,
        ];
    }

    private function transformInvoice(Invoice $invoice): array
    {
        return [
            'uuid' => $invoice->getUuid(),
            'invoice_number' => $invoice->getExternalCode(),
            'created_at' => $invoice->getCreatedAt()->format(DateFormat::FORMAT_YMD_HIS),
            'due_date' => $invoice->getDueDate()->format(DateFormat::FORMAT_YMD),
            'amount' => $invoice->getAmount()->getGross()->getMoneyValue(),
            'outstanding_amount' => $invoice->getOutstandingAmount()->getMoneyValue(),
        ];
    }
}
