<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApiV2;

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/orders",
 *     operationId="order_create_V2",
 *     summary="Create Order",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Back-end Order Creation"},
 *     x={"groups":{"publicV2"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/CreateOrderRequestV2"))
 *     ),
 *
 *     @OA\Response(response=200, description="Order successfully created", @OA\JsonContent(ref="#/components/schemas/OrderResponseV2")),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CreateOrderController
{
    public function execute(): void
    {
    }
}
