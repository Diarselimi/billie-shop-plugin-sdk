<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ApproveOrder\ApproveOrderRequest;
use App\Application\UseCase\ApproveOrder\ApproveOrderUseCase;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Post(
 *     path="/order/{uuid}/approve",
 *     operationId="order_approve",
 *     summary="Approve Order in Waiting State",
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
 *     @OA\Response(response=204, description="Order approved."),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class ApproveOrderController
{
    private ApproveOrderUseCase $useCase;

    public function __construct(ApproveOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $uuid): void
    {
        try {
            $this->useCase->execute(new ApproveOrderRequest($uuid));
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (WorkflowException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        }
    }
}
