<?php

namespace App\Http\Controller\PublicApiV2;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\CheckoutConfirmOrder\CheckoutConfirmOrderUseCase;
use App\Http\Factory\OrderResponseFactory;
use App\Http\RequestTransformer\CheckoutConfirmOrderRequestFactory;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
 *          @OA\Schema(ref="#/components/schemas/CheckoutConfirmOrderRequest"))
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
    private CheckoutConfirmOrderUseCase $useCase;

    private CheckoutConfirmOrderRequestFactory $confirmOrderRequestFactory;

    private OrderResponseFactory $responseFactory;

    public function __construct(
        CheckoutConfirmOrderUseCase $checkoutSessionUseCase,
        CheckoutConfirmOrderRequestFactory $confirmOrderRequestFactory,
        OrderResponseFactory $responseFactory
    ) {
        $this->useCase = $checkoutSessionUseCase;
        $this->confirmOrderRequestFactory = $confirmOrderRequestFactory;
        $this->responseFactory = $responseFactory;
    }

    public function execute(Request $request, string $sessionUuid): JsonResponse
    {
        $checkoutRequest = $this->confirmOrderRequestFactory->create($request, $sessionUuid);

        try {
            $response = $this->useCase->execute($checkoutRequest);
        } catch (OrderNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (WorkflowException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage());
        }

        return new JsonResponse(
            $this->responseFactory->create($response)->toArray(),
            JsonResponse::HTTP_ACCEPTED
        );
    }
}
