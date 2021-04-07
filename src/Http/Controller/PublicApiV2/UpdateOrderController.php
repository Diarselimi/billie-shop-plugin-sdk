<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApiV2;

use OpenApi\Annotations as OA;

/**
 * @OA\Patch(
 *     path="/orders/{uuid}",
 *     operationId="order_update",
 *     summary="Update Order",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Order Management"},
 *     x={"groups":{"publicV2"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/UpdateOrderRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Order successfully updated"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class UpdateOrderController
{
    public function execute(): void
    {
        return;
    }
}
