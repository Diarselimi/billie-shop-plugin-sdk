<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\CheckoutSessionConfirmException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\CheckoutConfirmOrder\CheckoutConfirmOrderUseCase;
use App\Application\UseCase\CheckoutConfirmOrder\CheckoutConfirmOrderFactory;
use App\DomainModel\Order\OrderRepositoryInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_MERCHANT")
 * @OA\Put(
 *     path="/checkout-session/{sessionUuid}/confirm",
 *     operationId="checkout_session_confirm",
 *     summary="Checkout Session Confirm",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Checkout Server"},
 *     x={"groups":{"public", "private"}},
 *
 *     @OA\Parameter(in="path", name="sessionUuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/CheckoutConfirmOrderRequest"))
 *     ),
 *
 *     @OA\Response(response=202, description="Order data successfully confirmed", @OA\JsonContent(ref="#/components/schemas/OrderResponse")),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CheckoutConfirmOrderController
{
    private $orderRepository;

    private $useCase;

    private $requestFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CheckoutConfirmOrderUseCase $checkoutSessionUseCase,
        CheckoutConfirmOrderFactory $factory
    ) {
        $this->orderRepository = $orderRepository;
        $this->useCase = $checkoutSessionUseCase;
        $this->requestFactory = $factory;
    }

    public function execute(Request $request, string $sessionUuid): JsonResponse
    {
        $checkoutRequest = $this->requestFactory->create(
            $request->request->get('amount', []),
            $request->request->get('debtor_company', []),
            $request->request->get('duration'),
            $sessionUuid
        );

        try {
            $orderResponse = $this->useCase->execute($checkoutRequest);
        } catch (OrderNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (CheckoutSessionConfirmException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }

        return new JsonResponse($orderResponse->toArray(), JsonResponse::HTTP_ACCEPTED);
    }
}
