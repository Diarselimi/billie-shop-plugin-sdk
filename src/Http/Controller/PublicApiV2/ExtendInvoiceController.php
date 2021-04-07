<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApiV2;

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/invoices/{uuid}/extend-duration",
 *     operationId="extend_invoice_duration",
 *     summary="Extend Invoice duration",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Invoices"},
 *     x={"groups":{"publicV2"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(properties={
 *                  @OA\Property(property="duration", ref="#/components/schemas/OrderDuration")
 *              })
 *          )
 *     ),
 *
 *     @OA\Response(response=204, description="Invoice extended successfully"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class ExtendInvoiceController
{
    public function execute(): void
    {
        return;
    }
}
