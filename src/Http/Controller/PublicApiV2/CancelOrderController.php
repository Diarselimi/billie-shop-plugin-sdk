<?php

namespace App\Http\Controller\PublicApiV2;

use App\Application\CommandBus;
use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\CancelOrder\CancelOrderException;
use App\Application\UseCase\CancelOrder\CancelOrderRequest;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted({"ROLE_AUTHENTICATED_AS_MERCHANT", "ROLE_CANCEL_ORDERS"})
 * @OA\Post(
 *     path="/orders/{id}/cancel",
 *     operationId="order_cancel_v2",
 *     summary="Cancel Order",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Order Management"},
 *     x={"groups":{"publicV2"}},
 *
 *     @OA\Parameter(in="path", name="id", @OA\Schema(type="integer"), required=true, description="Order ID or UUID"),
 *
 *     @OA\Response(response=204, description="Order successfully cancelled"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CancelOrderController
{
    private CommandBus $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function execute(string $id, Request $request): void
    {
        try {
            $orderRequest = new CancelOrderRequest(
                $id,
                $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID)
            );
            $this->commandBus->process($orderRequest);
        } catch (CancelOrderException | WorkflowException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}
