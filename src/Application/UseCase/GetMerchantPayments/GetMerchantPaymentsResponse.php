<?php

namespace App\Application\UseCase\GetMerchantPayments;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="GetMerchantPaymentsResponse", title="Merchant Payments Response", type="object", properties={
 *     @OA\Property(property="total", type="number", description="Total number of results"),
 *     @OA\Property(property="items", type="array", description="Merchant payment item", @OA\Items({
 *          @OA\Property(property="transaction_uuid", type="string"),
 *          @OA\Property(property="amount", type="number", type="float"),
 *          @OA\Property(property="transaction_date", type="string"),
 *          @OA\Property(property="is_allocated", type="boolean"),
 *          @OA\Property(property="transaction_counterparty_iban", type="string"),
 *          @OA\Property(property="transaction_counterparty_name", type="string"),
 *          @OA\Property(property="transaction_reference", type="string"),
 *          @OA\Property(property="payment_debtor_uuid", type="string"),
 *          @OA\Property(property="merchant_debtor", type="object", properties={
 *              @OA\Property(property="uuid", type="string", description="Uuid"),
 *              @OA\Property(property="company_name", type="string", description="Company Name")
 *          }),
 *      }))
 * })
 */
class GetMerchantPaymentsResponse implements ArrayableInterface
{
    private $items;

    private $total;

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): GetMerchantPaymentsResponse
    {
        $this->items = $items;

        return $this;
    }

    public function setTotal(int $total): GetMerchantPaymentsResponse
    {
        $this->total = $total;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'items' => $this->items,
        ];
    }
}
