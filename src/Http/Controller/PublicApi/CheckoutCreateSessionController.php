<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\CheckoutCreateSession\CheckoutCreateSessionResponse;
use App\Application\UseCase\CheckoutCreateSession\CheckoutCreateSessionRequest;
use App\Application\UseCase\CheckoutCreateSession\CheckoutCreateSessionUseCase;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_MERCHANT")
 * @OA\Post(
 *     path="/checkout-session",
 *     operationId="checkout_session_create",
 *     summary="Checkout Session Create",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Checkout Server"},
 *     x={"groups":{"public", "private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(type="object", properties={
 *              @OA\Property(property="merchant_customer_id", ref="#/components/schemas/TinyText", description="Debtor ID in the merchant system.")
 *          }))
 *     ),
 *
 *     @OA\Response(response=200, description="Checkout session created.", @OA\JsonContent(
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
class CheckoutCreateSessionController
{
    private $useCase;

    public function __construct(CheckoutCreateSessionUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): CheckoutCreateSessionResponse
    {
        $createCheckoutSession = (new CheckoutCreateSessionRequest())
            ->setMerchantId($request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID))
            ->setMerchantDebtorExternalId($request->request->get('merchant_customer_id'));

        return $this->useCase->execute($createCheckoutSession);
    }
}
