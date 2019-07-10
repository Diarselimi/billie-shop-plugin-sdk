<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\OrderDeclinedException;
use App\Application\UseCase\CheckoutSessionCreateOrder\CheckoutSessionCreateOrderUseCase;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\Http\ApiError\ApiError;
use App\Http\ApiError\ApiErrorResponse;
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
 *     summary="Checkout Session Authorize",
 *     description="Fills the required order information for the given checkout session. The order will then need to be confirmed by the merchant aftewards.",
 *
 *     tags={"Order Creation"},
 *     x={"groups":{"checkout-client"}},
 *
 *     @OA\Parameter(in="path", name="sessionUuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/CreateOrderRequest"))
 *     ),
 *
 *     @OA\Response(response=201, description="Order created with 'authorized' state, but a merchant confirmation is needed."),
 *     @OA\Response(response=400, description="Invalid request data or order declined.", @OA\JsonContent(
 *          title="Checkout Authorize Error",
 *          type="object",
 *          allOf={
 *              @OA\Schema(ref="#/components/schemas/ErrorsObject")
 *          },
 *          properties={
 *             @OA\Property(
 *               property="reasons",
 *               type="array",
 *               description="Decline reasons",
 *               @OA\Items(ref="#/components/schemas/OrderDeclineReason")
 *            )
 *          }
 *     )),
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
        CheckoutSessionCreateOrderUseCase $useCase,
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

        try {
            $this->useCase->execute($useCaseRequest);
        } catch (OrderDeclinedException $exception) {
            return new ApiErrorResponse(
                [new ApiError(
                    $exception->getMessage(),
                    'order_declined',
                    null,
                    ['reasons' => $exception->getReasons()]
                )],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        return new Response('', JsonResponse::HTTP_CREATED);
    }
}
