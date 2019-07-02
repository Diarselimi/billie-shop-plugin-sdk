<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\CancelOrder\CancelOrderRequest;
use App\Application\UseCase\CancelOrder\CancelOrderUseCase;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Post(
 *     path="/order/{id}/cancel",
 *     operationId="order_cancel",
 *     summary="Cancel Order",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Orders"},
 *     x={"groups":{"standard", "checkout-server"}},
 *
 *     @OA\Parameter(in="path", name="id", @OA\Schema(type="integer"), required=true, description="Order ID or UUID"),
 *
 *     @OA\Response(response=204, description="Order cancelled."),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CancelOrderController
{
    private $useCase;

    public function __construct(CancelOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $id, Request $request): void
    {
        try {
            $orderRequest = new CancelOrderRequest(
                $id,
                $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID)
            );
            $this->useCase->execute($orderRequest);
        } catch (FraudOrderException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}
