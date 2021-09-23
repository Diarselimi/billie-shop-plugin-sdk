<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\CommandBus;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\TriggerFailedOrderNotifications\TriggerFailedOrderNotificationsRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/order/{uuid}/trigger-failed-notifications",
 *     operationId="trigger_failed_order_notifications",
 *     summary="Trigger all failed order notifications",
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
 *     @OA\Response(response=204, description="Order failed notifications were triggered successfully."),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class TriggerFailedOrderNotificationsController
{
    private CommandBus $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function execute(string $uuid): void
    {
        try {
            $this->commandBus->process(new TriggerFailedOrderNotificationsRequest($uuid));
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}
