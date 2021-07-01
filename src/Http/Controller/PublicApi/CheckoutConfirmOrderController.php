<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\CheckoutConfirmOrder\CheckoutConfirmOrderUseCase;
use App\Http\RequestTransformer\CheckoutConfirmOrderRequestFactory;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
 *     x={"groups":{"publicV1", "private"}},
 *
 *     @OA\Parameter(in="path", name="sessionUuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/CheckoutConfirmOrderRequestLegacy"))
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
    private $useCase;

    private $confirmOrderRequestFactory;

    public function __construct(
        CheckoutConfirmOrderUseCase $checkoutSessionUseCase,
        CheckoutConfirmOrderRequestFactory $confirmOrderRequestFactory
    ) {
        $this->useCase = $checkoutSessionUseCase;
        $this->confirmOrderRequestFactory = $confirmOrderRequestFactory;
    }

    public function execute(Request $request, string $sessionUuid): JsonResponse
    {
        $checkoutRequest = $this->confirmOrderRequestFactory->create($request, $sessionUuid);

        try {
            $orderResponse = $this->useCase->execute($checkoutRequest);
        } catch (OrderNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (WorkflowException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage());
        }

        return new JsonResponse($orderResponse->toArray(), JsonResponse::HTTP_ACCEPTED);
    }
}
