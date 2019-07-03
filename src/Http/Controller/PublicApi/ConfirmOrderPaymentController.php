<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\FraudOrderException;
use App\Application\UseCase\ConfirmOrderPayment\ConfirmOrderPaymentRequest;
use App\Application\UseCase\ConfirmOrderPayment\ConfirmOrderPaymentUseCase;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @OA\Post(
 *     path="/order/{id}/confirm-payment",
 *     operationId="order_payment_confirm",
 *     summary="Confirm Order Payment",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Orders"},
 *     x={"groups":{"standard", "checkout-server", "dashboard"}},
 *
 *     @OA\Parameter(in="path", name="id", @OA\Schema(ref="#/components/schemas/TinyText"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(type="object", properties={
 *              @OA\Property(property="amount", type="number", format="float", minimum=0.1)
 *          }))
 *     ),
 *
 *     @OA\Response(response=204, description="Order payment successfully confirmed"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class ConfirmOrderPaymentController
{
    private $useCase;

    public function __construct(ConfirmOrderPaymentUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $id, Request $request): void
    {
        try {
            $orderRequest = new ConfirmOrderPaymentRequest(
                $id,
                $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
                $request->request->get('amount')
            );
            $this->useCase->execute($orderRequest);
        } catch (FraudOrderException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        }
    }
}
