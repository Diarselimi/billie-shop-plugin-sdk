<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\CheckoutSessionCreateOrder\CheckoutSessionCreateOrderUseCase;
use App\DomainModel\OrderResponse\CheckoutSessionAuthorizeResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\Http\Authentication\UserProvider;
use App\Http\RequestHandler\CreateOrderRequestFactory;
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
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="sessionUuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/CreateOrderRequest"))
 *     ),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/CheckoutSessionAuthorizeResponse"), description="Order created with 'authorized' state, but a merchant confirmation is needed."),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class AuthorizeCheckoutSessionController
{
    private $useCase;

    private $userProvider;

    private $orderRequestFactory;

    private $responseFactory;

    public function __construct(
        CheckoutSessionCreateOrderUseCase $useCase,
        UserProvider $userProvider,
        CreateOrderRequestFactory $orderRequestFactory,
        OrderResponseFactory $responseFactory
    ) {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
        $this->orderRequestFactory = $orderRequestFactory;
        $this->responseFactory = $responseFactory;
    }

    public function execute(Request $request): CheckoutSessionAuthorizeResponse
    {
        $checkoutSession = $this->userProvider->getCheckoutUser()->getCheckoutSession();
        $useCaseRequest = $this->orderRequestFactory->createForAuthorizeCheckoutSession($request, $checkoutSession);
        $orderContainer = $this->useCase->execute($useCaseRequest);

        return $this->responseFactory->createAuthorizeResponse($orderContainer);
    }
}
