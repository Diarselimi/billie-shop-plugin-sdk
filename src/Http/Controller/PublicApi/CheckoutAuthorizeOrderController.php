<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\CheckoutAuthorizeOrder\CheckoutAuthorizeOrderUseCase;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderResponse\CheckoutAuthorizeOrderResponse;
use App\Http\Authentication\UserProvider;
use App\Http\HttpConstantsInterface;
use App\Http\RequestTransformer\CreateOrder\CreateOrderRequestFactory;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_CHECKOUT_USER")
 * @OA\Put(
 *     path="/checkout-session/{sessionUuid}/authorize",
 *     operationId="checkout_session_authorize",
 *     summary="Checkout Session Authorize",
 *     description="Fills the required order information for the given checkout session. The order will then need to be confirmed by the merchant aftewards.",
 *
 *     tags={"Checkout Client"},
 *     x={"groups":{"private", "checkout-client", "amazon-apigateway-integration"}},
 *
 *     @OA\Parameter(in="path", name="sessionUuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/LegacyCreateOrderRequest"))
 *     ),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/CheckoutAuthorizeOrderResponse"), description="Order created with 'authorized' state, but a merchant confirmation is needed."),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CheckoutAuthorizeOrderController
{
    private CheckoutAuthorizeOrderUseCase $useCase;

    private UserProvider $userProvider;

    private CreateOrderRequestFactory $orderRequestFactory;

    public function __construct(
        CheckoutAuthorizeOrderUseCase $useCase,
        UserProvider $userProvider,
        CreateOrderRequestFactory $orderRequestFactory
    ) {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
        $this->orderRequestFactory = $orderRequestFactory;
    }

    public function execute(Request $request): CheckoutAuthorizeOrderResponse
    {
        $request->attributes->set(
            HttpConstantsInterface::REQUEST_ATTRIBUTE_CREATION_SOURCE,
            OrderEntity::CREATION_SOURCE_CHECKOUT
        );
        $checkoutSession = $this->userProvider->getCheckoutUser()->getCheckoutSession();
        $useCaseRequest = $this->orderRequestFactory->createForAuthorizeCheckoutSession($request, $checkoutSession);

        return $this->useCase->execute($useCaseRequest);
    }
}
