<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApiV2;

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/invoices",
 *     operationId="invoice_create",
 *     summary="Create Invoice",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Invoices"},
 *     x={"groups":{"publicV2"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/CreateInvoiceRequest"))
 *     ),
 *
 *     @OA\Response(response=201, description="Invoice successfully created"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CreateInvoiceController
{
    public function execute(): void
    {
        return;
    }
}
