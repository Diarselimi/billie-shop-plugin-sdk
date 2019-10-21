<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\CheckoutSessionConfirmException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\CheckoutSessionConfirmOrder\CheckoutSessionConfirmOrderRequest;
use App\Application\UseCase\CheckoutSessionConfirmOrder\CheckoutSessionConfirmUseCase;
use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
use App\DomainModel\Order\OrderRepositoryInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
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
 *          @OA\Schema(ref="#/components/schemas/CheckoutSessionConfirmOrderRequest"))
 *     ),
 *
 *     @OA\Response(response=202, description="Order data successfully confirmed", @OA\JsonContent(ref="#/components/schemas/OrderResponse")),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class ConfirmCheckoutSessionController
{
    private $orderRepository;

    private $useCase;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CheckoutSessionConfirmUseCase $checkoutSessionUseCase
    ) {
        $this->orderRepository = $orderRepository;
        $this->useCase = $checkoutSessionUseCase;
    }

    public function execute(Request $request, string $sessionUuid): JsonResponse
    {
        $checkoutRequest = (new CheckoutSessionConfirmOrderRequest())
            ->setAmount(
                (new CreateOrderAmountRequest())
                    ->setNet($request->request->get('amount')['net'] ?? null)
                    ->setGross($request->request->get('amount')['gross'] ?? null)
                    ->setTax($request->request->get('amount')['tax'] ?? null)
            )
            ->setDuration($request->request->get('duration'))
            ->setSessionUuid($sessionUuid)
        ;

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
