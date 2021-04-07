<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateInvoice;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="UpdateInvoiceRequest", title="Invoice Update Request", required={"external_code", "invoice_url"},
 *     properties={
 *          @OA\Property(property="external_code", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="invoice_url", ref="#/components/schemas/TinyText", nullable=true)
 *     }
 * )
 */
class UpdateInvoiceRequest
{
}
