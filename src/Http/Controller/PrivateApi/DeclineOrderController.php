<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\CommandBus;
use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\DeclineOrder\DeclineOrderRequest;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Post(
 *     path="/order/{uuid}/decline",
 *     operationId="order_decline",
 *     summary="Decline Order in Waiting State",
 *
 *     tags={"Support"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(
 *          in="path",
 *          name="uuid",
 *          @OA\Schema(ref="#/components/schemas/UUID"),
 *          description="Order UUID",
 *          required=true
 *     ),
 *
 *     @OA\Response(response=204, description="Order declined."),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class DeclineOrderController
{
    private CommandBus $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function execute(string $uuid): void
    {
        try {
            $this->commandBus->process(new DeclineOrderRequest($uuid));
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (WorkflowException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        }
    }
}
