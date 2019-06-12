<?php

namespace App\Http\Controller;

use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\Http\Authentication\User;
use App\Http\RequestHandler\CreateOrderRequestFactory;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

/**
 * @OA\Put(
 *     path="/checkout-session/{sessionUuid}/authorize",
 *     operationId="checkout_session_authorize",
 *     summary="Authorize Checkout Session",
 *
 *     tags={"Checkout API"},
 *     x={"groups":{"public"}},
 *
 *     @OA\Parameter(in="path", name="sessionUuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/CreateOrderRequest"))
 *     ),
 *
 *     @OA\Response(response=201, description="Order was created, but a confirmation is needed."),
 *     @OA\Response(response=400, description="Invalid request data or order declined.", @OA\JsonContent(ref="#/components/schemas/CheckoutAuthorizeErrorObject")),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class AuthorizeCheckoutSessionController
{
    private $useCase;

    private $security;

    private $orderRequestFactory;

    private $responseFactory;

    public function __construct(
        CreateOrderUseCase $useCase,
        Security $security,
        CreateOrderRequestFactory $orderRequestFactory,
        OrderResponseFactory $responseFactory
    ) {
        $this->useCase = $useCase;
        $this->security = $security;
        $this->orderRequestFactory = $orderRequestFactory;
        $this->responseFactory = $responseFactory;
    }

    public function execute(Request $request): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $useCaseRequest = $this->orderRequestFactory
            ->createForAuthorizeCheckoutSession($request, $user->getCheckoutSession());

        $orderContainer = $this->useCase->execute($useCaseRequest);

        // TODO: use own Authorize UseCase, moving this check inside or fail with exception.
        if ($orderContainer->getOrder()->getState() === OrderStateManager::STATE_AUTHORIZED) {
            return new Response('', JsonResponse::HTTP_CREATED);
        }

        // TODO: this should be an error, using the common error response format.
        // TODO: The use case should throw an "OrderDeclinedException" and the controller should catch it, adding reasons contained in the exception.
        $reasons = $this->responseFactory->createAuthorizeResponse($orderContainer)->toArray()['reasons'];

        return new JsonResponse(
            [
                'error' => 'Order declined',
                'code' => 'order_declined',
                'reasons' => $reasons,

            ],
            JsonResponse::HTTP_BAD_REQUEST
        );
    }
}
