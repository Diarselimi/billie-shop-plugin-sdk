<?php

namespace App\Http\Controller\PublicApiV2;

use OpenApi\Annotations as OA;

/**
 * @OA\Put(
 *     path="/checkout-sessions/{sessionUuid}/confirm",
 *     operationId="checkout_session_confirm",
 *     summary="Checkout Session Confirm",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Checkout Server"},
 *     x={"groups":{"publicV2"}},
 *
 *     @OA\Parameter(in="path", name="sessionUuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/CheckoutConfirmOrderRequestV2"))
 *     ),
 *
 *     @OA\Response(response=202, description="Order data successfully confirmed", @OA\JsonContent(ref="#/components/schemas/Order")),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CheckoutConfirmOrderController
{
    public function execute(): void
    {
    }
}
