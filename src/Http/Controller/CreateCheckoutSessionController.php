<?php

namespace App\Http\Controller;

use App\Application\UseCase\CheckoutSession\CreateCheckoutSessionRequest;
use App\Application\UseCase\CheckoutSession\CreateCheckoutSessionUseCase;
use App\Application\UseCase\Response\CheckoutSessionResponse;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;

/**
 * @OA\Post(
 *     path="/checkout-session",
 *     operationId="checkout_session_create",
 *     summary="Create Checkout Session",
 *     security={{"oauth2"={}}, {"apiKey"={}}},
 *
 *     tags={"Checkout API"},
 *     x={"groups":{"public"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(type="object", properties={
 *              @OA\Property(property="merchant_customer_id", ref="#/components/schemas/TinyText", description="Debtor ID in the merchant system.")
 *          }))
 *     ),
 *
 *     @OA\Response(response=200, description="Order payment successfully confirmed.", @OA\JsonContent(
 *          type="object",
 *          required={"id"},
 *          properties={
 *              @OA\Property(property="id", ref="#/components/schemas/UUID", description="Checkout Session Token")
 *          }
 *     )),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CreateCheckoutSessionController
{
    private $useCase;

    public function __construct(CreateCheckoutSessionUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): CheckoutSessionResponse
    {
        $createCheckoutSession = (new CreateCheckoutSessionRequest())
            ->setMerchantId($request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID))
            ->setMerchantDebtorExternalId($request->request->get('merchant_customer_id'));

        return $this->useCase->execute($createCheckoutSession);
    }
}
