<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateInvoice;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="CreateInvoiceRequest", title="Order Creation Request", required={"amount", "duration", "debtor_company", "debtor_person"},
 *     properties={
 *          @OA\Property(
 *              property="orders",
 *              type="array",
 *              nullable=true,
 *              @OA\Items(ref="#/components/schemas/TinyText")
 *          ),
 *          @OA\Property(property="external_code", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="invoice_url", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="shipping_document_url", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="amount", ref="#/components/schemas/AmountDTO"),
 *          @OA\Property(
 *              property="line_items",
 *              type="array",
 *              nullable=true,
 *              @OA\Items(ref="#/components/schemas/LineItemsRequest")
 *          )
 *     }
 * )
 */
class CreateInvoiceRequest
{
    public function create(): void
    {
    }
}
