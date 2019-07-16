<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\Application\UseCase\DeclineOrder\DeclineOrderRequest;
use App\Application\UseCase\DeclineOrder\DeclineOrderUseCase;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Post(
 *     path="/order/{uuid}/decline",
 *     operationId="order_decline",
 *     summary="Decline Order in Waiting State",
 *
 *     tags={"Order Management"},
 *     x={"groups":{"support", "salesforce"}},
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
    private $useCase;

    public function __construct(DeclineOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $uuid): void
    {
        try {
            $this->useCase->execute(new DeclineOrderRequest($uuid));
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (OrderWorkflowException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        }
    }
}
